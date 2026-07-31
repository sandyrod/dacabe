<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ConciliacionBancariaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private int $year;
    private int $month;
    private string $bancoCodigo;

    public function __construct(int $year, int $month, string $bancoCodigo)
    {
        $this->year = $year;
        $this->month = $month;
        $this->bancoCodigo = $bancoCodigo;
    }

    public function collection(): Collection
    {
        $periodo = DB::connection('company')
            ->table('conciliacion_bancaria_periodos')
            ->where('anio', $this->year)
            ->where('mes', $this->month)
            ->where('banco_codigo', $this->bancoCodigo)
            ->first();

        if (!$periodo) {
            return collect();
        }

        $rows = collect();

        $salidas = DB::connection('company')
            ->table('pagos as pag')
            ->join('pagos_pedidos as pp', 'pp.pago_id', '=', 'pag.id')
            ->leftJoin('pedidos_facturas as pf', 'pf.pedido_id', '=', 'pp.pedido_id')
            ->leftJoin('pedidos as ped', 'ped.id', '=', 'pp.pedido_id')
            ->whereYear('pag.fecha', $this->year)
            ->whereMonth('pag.fecha', $this->month)
            ->when($this->bancoCodigo !== '', function ($q) {
                $q->where('pag.banco_codigo', $this->bancoCodigo);
            })
            ->selectRaw('pag.fecha as fecha')
            ->selectRaw('CONCAT("PAGO DE FACT Nro. ", COALESCE(pf.factura, "S/F"), " ", COALESCE(ped.descripcion, "SIN CLIENTE")) as descripcion')
            ->selectRaw('CASE
                WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(CONVERT(pag.moneda_pago USING utf8mb4), "")), "í", "i"), "á", "a")) IN ("bolivares", "bs")
                    THEN COALESCE((COALESCE(pp.monto, 0) * COALESCE(pag.rate, 0)) + COALESCE(pp.iva, 0) + (COALESCE(pp.ajustes_monto, 0) * COALESCE(pag.rate, 0)), 0)
                ELSE COALESCE(pp.monto, 0)
            END as monto')
            ->selectRaw('CASE
                WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(CONVERT(pag.moneda_pago USING utf8mb4), "")), "í", "i"), "á", "a")) IN ("bolivares", "bs")
                    THEN "Bs. "
                ELSE "$ "
            END as monto_prefijo')
            ->orderBy('pag.fecha')
            ->orderBy('pag.id')
            ->get();

        foreach ($salidas as $item) {
            $rows->push([
                'fecha' => $item->fecha,
                'descripcion' => $item->descripcion,
                'entra' => '',
                'sale' => (string) ($item->monto_prefijo ?? '') . number_format((float) $item->monto, 2, ',', '.'),
            ]);
        }

        $entradas = DB::connection('company')
            ->table('conciliacion_bancaria_entradas')
            ->where('periodo_id', $periodo->id)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        foreach ($entradas as $item) {
            $rows->push([
                'fecha' => $item->fecha,
                'descripcion' => $item->descripcion,
                'entra' => number_format((float) $item->monto, 2, ',', '.'),
                'sale' => '',
            ]);
        }

        return $rows->sortBy('fecha')->values();
    }

    public function headings(): array
    {
        return ['Fecha', 'Descripcion', 'Entra', 'Sale'];
    }
}
