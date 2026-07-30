<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ConciliacionBancariaExport;
use App\Http\Controllers\Controller;
use App\Models\PagoDestino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ConciliacionLibroBancoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $bancoCodigo = (string) $request->input('banco_codigo', '');

        if ($bancoCodigo === '') {
            $bancoCodigo = (string) $this->getDefaultBancoCodigo();
        }

        $periodo = DB::connection('company')
            ->table('conciliacion_bancaria_periodos')
            ->where('anio', $year)
            ->where('mes', $month)
            ->where('banco_codigo', $bancoCodigo)
            ->first();

        if (!$periodo) {
            $periodoId = DB::connection('company')->table('conciliacion_bancaria_periodos')->insertGetId([
                'anio' => $year,
                'mes' => $month,
                'banco_codigo' => $bancoCodigo,
                'saldo_inicial' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $periodo = DB::connection('company')
                ->table('conciliacion_bancaria_periodos')
                ->where('id', $periodoId)
                ->first();
        }

        $conceptos = DB::connection('company')
            ->table('conciliacion_bancaria_conceptos')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $salidas = DB::connection('company')
            ->table('pagos as pag')
            ->join('pagos_pedidos as pp', 'pp.pago_id', '=', 'pag.id')
            ->leftJoin('pedidos_facturas as pf', 'pf.pedido_id', '=', 'pp.pedido_id')
            ->leftJoin('pedidos as ped', 'ped.id', '=', 'pp.pedido_id')
            ->leftJoin('pago_destinos as pd', 'pd.id', '=', 'pag.pago_destino_id')
            ->whereYear('pag.fecha', $year)
            ->whereMonth('pag.fecha', $month)
            ->when($bancoCodigo !== '', function ($query) use ($bancoCodigo) {
                $selectedValue = trim((string) $bancoCodigo);
                $normalizedValue = self::normalizeFilterValue($selectedValue);

                $query->where(function ($q) use ($selectedValue, $normalizedValue) {
                    $q->where('pag.banco_codigo', $selectedValue)
                        ->orWhereRaw('TRIM(COALESCE(CONVERT(pd.nombre USING utf8mb4), "")) = ?', [$selectedValue])
                        ->orWhereRaw('TRIM(COALESCE(CONVERT(pag.banco_codigo USING utf8mb4), "")) = ?', [$selectedValue]);

                    if ($normalizedValue !== '') {
                        $q->orWhereRaw("REPLACE(REPLACE(LOWER(TRIM(COALESCE(CONVERT(pd.nombre USING utf8mb4), ''))), ' ', ''), '$', '') = ?", [$normalizedValue])
                            ->orWhereRaw("REPLACE(REPLACE(LOWER(TRIM(COALESCE(CONVERT(pag.banco_codigo USING utf8mb4), ''))), ' ', ''), '$', '') = ?", [$normalizedValue]);
                    }
                });
            })
            ->selectRaw('pag.fecha as fecha')
            ->selectRaw('CONCAT("PAGO DE FACT Nro. ", COALESCE(pf.factura, "S/F"), " ", COALESCE(ped.descripcion, "SIN CLIENTE")) as descripcion')
            ->selectRaw('COALESCE(pp.monto * COALESCE(pag.rate, 0), 0) as monto')
            ->orderBy('pag.fecha')
            ->orderBy('pag.id')
            ->get();

        $manualMovimientos = DB::connection('company')
            ->table('conciliacion_bancaria_entradas')
            ->where('periodo_id', $periodo->id)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        if ($this->hasTipoMovimientoColumn()) {
            $entradas = $manualMovimientos->where('tipo_movimiento', 'entrada');
            $salidasManuales = $manualMovimientos->where('tipo_movimiento', 'salida');
        } else {
            $entradas = $manualMovimientos;
            $salidasManuales = collect();
        }

        $totalEntradas = (float) $entradas->sum('monto');
        $totalPagos = (float) $salidas->sum('monto');
        $totalSalidasManuales = (float) $salidasManuales->sum('monto');

        $totales = [
            'saldo_inicial' => (float) ($periodo->saldo_inicial ?? 0),
            'total_cargos' => $totalPagos + $totalEntradas,
            'total_abonos' => $totalSalidasManuales,
            'saldo_final' => (float) ($periodo->saldo_inicial ?? 0) + $totalPagos + $totalEntradas - $totalSalidasManuales,
        ];

        return view('admin.conciliacion_libro_banco.index', [
            'periodo' => $periodo,
            'conceptos' => $conceptos,
            'salidas' => $salidas,
            'entradas' => $entradas,
            'salidas_manuales' => $salidasManuales,
            'totales' => $totales,
            'bancos' => $this->getBancos(),
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'selectedBancoCodigo' => $bancoCodigo,
        ]);
    }

    public function saveSaldoInicial(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|integer|min:1',
            'saldo_inicial' => 'required|numeric',
        ]);

        DB::connection('company')
            ->table('conciliacion_bancaria_periodos')
            ->where('id', $request->input('periodo_id'))
            ->update([
                'saldo_inicial' => (float) $request->input('saldo_inicial'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Saldo inicial actualizado correctamente.');
    }

    public function storeEntrada(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'concepto_id' => 'nullable|integer|min:1',
            'tipo_movimiento' => 'required|in:entrada,salida',
        ]);

        $tipoMovimiento = $request->input('tipo_movimiento') === 'salida' ? 'salida' : 'entrada';

        $entryData = [
            'periodo_id' => $request->input('periodo_id'),
            'fecha' => $request->input('fecha'),
            'descripcion' => trim((string) $request->input('descripcion')),
            'monto' => (float) $request->input('monto'),
            'concepto_id' => $request->filled('concepto_id') ? (int) $request->input('concepto_id') : null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->hasTipoMovimientoColumn()) {
            $entryData['tipo_movimiento'] = $tipoMovimiento;
        }

        DB::connection('company')->table('conciliacion_bancaria_entradas')->insert($entryData);

        return back()->with('success', $tipoMovimiento === 'salida' ? 'Salida agregada correctamente.' : 'Entrada agregada correctamente.');
    }

    public function updateEntrada(Request $request, $entradaId)
    {
        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'concepto_id' => 'nullable|integer|min:1',
        ]);

        DB::connection('company')
            ->table('conciliacion_bancaria_entradas')
            ->where('id', $entradaId)
            ->update([
                'fecha' => $request->input('fecha'),
                'descripcion' => trim((string) $request->input('descripcion')),
                'monto' => (float) $request->input('monto'),
                'concepto_id' => $request->filled('concepto_id') ? (int) $request->input('concepto_id') : null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Entrada actualizada correctamente.');
    }

    public function destroyEntrada($entradaId)
    {
        DB::connection('company')->table('conciliacion_bancaria_entradas')->where('id', $entradaId)->delete();

        return back()->with('success', 'Entrada eliminada correctamente.');
    }

    public function storeConcepto(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:120',
            'monto_sugerido' => 'nullable|numeric',
            'monto_fijo' => 'required|boolean',
        ]);

        DB::connection('company')->table('conciliacion_bancaria_conceptos')->insert([
            'nombre' => trim((string) $request->input('nombre')),
            'monto_sugerido' => $request->filled('monto_sugerido') ? (float) $request->input('monto_sugerido') : null,
            'monto_fijo' => (int) $request->input('monto_fijo', 0),
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Concepto guardado correctamente.');
    }

    public function export(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $bancoCodigo = (string) $request->input('banco_codigo', '');

        return Excel::download(new ConciliacionBancariaExport($year, $month, $bancoCodigo), 'conciliacion_bancaria_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx');
    }

    private function hasTipoMovimientoColumn(): bool
    {
        try {
            return Schema::connection('company')->hasColumn('conciliacion_bancaria_entradas', 'tipo_movimiento');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function normalizeFilterValue($value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = trim((string) $value);
        $normalized = mb_strtolower($normalized, 'UTF-8');
        $normalized = str_replace(['$', ' ', '-', '_', '.', ',', '/', '\\'], '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized);

        return $normalized;
    }

    private function getBancos(): array
    {
        $destinos = PagoDestino::query()
            ->orderBy('nombre')
            ->get(['nombre']);

        $items = [];

        foreach ($destinos as $destino) {
            $nombre = trim((string) ($destino->nombre ?? ''));
            if ($nombre === '') {
                continue;
            }

            $items[] = (object) [
                'CODIGO' => $nombre,
                'NOMBRE' => $nombre,
            ];
        }

        if ($items === []) {
            $items[] = (object) [
                'CODIGO' => 'SIN DESTINO',
                'NOMBRE' => 'SIN DESTINO',
            ];
        }

        return $items;
    }

    private function getDefaultBancoCodigo(): string
    {
        $destino = PagoDestino::query()
            ->orderBy('nombre')
            ->value('nombre');

        return trim((string) ($destino ?? '')) !== '' ? trim((string) $destino) : 'SIN DESTINO';
    }
}
