<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderBanco;
use App\Models\OrderTpago;
use App\Models\PagoDestino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConciliacionBancariaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $baseQuery = $this->buildFilteredBaseQuery($request);
        $detallePagos = $this->buildDetallePagosSubquery();

        $pagos = (clone $baseQuery)
            ->leftJoinSub($detallePagos, 'dp', function ($join) {
                $join->on('dp.pago_id', '=', 'pag.id');
            })
            ->selectRaw('pag.id')
            ->selectRaw('pag.fecha')
            ->selectRaw('pag.referencia')
            ->selectRaw('pag.estatus')
            ->selectRaw('pag.observaciones as motivo_ajuste')
            ->selectRaw('pag.moneda_pago')
            ->selectRaw('COALESCE(pag.rate, 0) as tasa')
            ->selectRaw('pag.tpago_id')
            ->selectRaw('tp.DPAGO as tipo_pago')
            ->selectRaw('pag.banco_codigo')
            ->selectRaw('bo.NOMBRE as banco_origen')
            ->selectRaw('pag.pago_destino_id')
            ->selectRaw('pd.nombre as destino_pago')
            ->selectRaw('COALESCE(v.codigo, "") as vendedor_codigo')
            ->selectRaw('COALESCE(u.name, "Sin asignar") as vendedor_nombre')
            ->selectRaw('COALESCE(dp.monto_usd, 0) as monto_usd')
            ->selectRaw('COALESCE(dp.monto_bs, 0) as monto_bs')
            ->selectRaw('COALESCE(dp.pedidos, "") as pedidos')
            ->selectRaw('COALESCE(dp.facturas, "") as facturas')
            ->selectRaw('COALESCE(dp.clientes, "") as clientes')
            ->orderByDesc('pag.fecha')
            ->orderByDesc('pag.id')
            ->paginate(25)
            ->appends($request->query());

        $resumen = (clone $baseQuery)
            ->leftJoinSub($detallePagos, 'dp', function ($join) {
                $join->on('dp.pago_id', '=', 'pag.id');
            })
            ->selectRaw('COUNT(DISTINCT pag.id) as total_pagos')
            ->selectRaw('COALESCE(SUM(dp.monto_usd), 0) as total_haber_usd')
            ->selectRaw('COALESCE(SUM(dp.monto_bs), 0) as total_haber_bs')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pag.estatus, "")) = "APROBADO" THEN dp.monto_usd ELSE 0 END), 0) as total_aprobado_usd')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pag.estatus, "")) = "APROBADO" THEN dp.monto_bs ELSE 0 END), 0) as total_aprobado_bs')
            ->first();

        $tablaMovimientosDisponible = Schema::connection('company')->hasTable('conciliacion_bancaria_movimientos');
        $balanceDestinoTipo = $this->buildBalanceDestinoTipo($request, $baseQuery, $tablaMovimientosDisponible);

        $vendedores = $this->getVendedores();
        $tiposPago = OrderTpago::orderBy('DPAGO')->get();
        $destinos = PagoDestino::orderBy('nombre')->get();
        $bancos = OrderBanco::orderBy('NOMBRE')->get();
        $filtrosBancoDestino = $this->buildBancoDestinoFilterOptions($bancos, $destinos);

        return view('admin.conciliacion_bancaria.index', [
            'pagos' => $pagos,
            'resumen' => $resumen,
            'balanceDestinoTipo' => $balanceDestinoTipo,
            'vendedores' => $vendedores,
            'tiposPago' => $tiposPago,
            'destinos' => $destinos,
            'bancos' => $bancos,
            'filtrosBancoDestino' => $filtrosBancoDestino,
            'tablaMovimientosDisponible' => $tablaMovimientosDisponible,
        ]);
    }

    public function reclasificar(Request $request, $pagoId)
    {
        $request->validate([
            'pago_destino_id' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:500',
        ]);

        $pago = DB::connection('company')->table('pagos')->where('id', $pagoId)->first();
        if (!$pago) {
            return back()->with('error', 'No se encontró el pago a reclasificar.');
        }

        $nuevoDestino = (int) $request->input('pago_destino_id');
        $motivo = trim((string) $request->input('motivo', ''));

        $cambioDestino = (int) $pago->pago_destino_id !== $nuevoDestino;

        if (!$cambioDestino) {
            return back()->with('info', 'No se detectaron cambios en la clasificación del pago.');
        }

        $totales = DB::connection('company')
            ->table('pagos_pedidos')
            ->where('pago_id', $pagoId)
            ->selectRaw('COALESCE(SUM(monto), 0) as monto_usd')
            ->first();

        $montoUsd = (float) ($totales->monto_usd ?? 0);
        $tasa = (float) ($pago->rate ?? 0);
        $montoBs = $montoUsd * $tasa;

        DB::connection('company')->transaction(function () use (
            $pago,
            $pagoId,
            $nuevoDestino,
            $motivo,
            $montoUsd,
            $montoBs,
            $tasa,
            $cambioDestino,
        ) {
            DB::connection('company')->table('pagos')->where('id', $pagoId)->update([
                'pago_destino_id' => $nuevoDestino,
                'observaciones' => $motivo !== ''
                    ? trim((string) $motivo)
                    : (isset($pago->observaciones) ? $pago->observaciones : null),
                'updated_at' => now(),
            ]);

            if (Schema::connection('company')->hasTable('conciliacion_bancaria_movimientos')) {
                DB::connection('company')->table('conciliacion_bancaria_movimientos')->insert([
                    'pago_id' => $pagoId,
                    'fecha_pago' => $pago->fecha,
                    'origen_destino_id' => $pago->pago_destino_id,
                    'destino_destino_id' => $nuevoDestino,
                    'origen_tpago_id' => $pago->tpago_id,
                    'destino_tpago_id' => $pago->tpago_id,
                    'origen_banco_codigo' => $pago->banco_codigo,
                    'destino_banco_codigo' => $pago->banco_codigo,
                    'monto_usd' => $montoUsd,
                    'monto_bs' => $montoBs,
                    'tasa' => $tasa,
                    'moneda_pago' => $pago->moneda_pago,
                    'motivo' => $motivo !== '' ? $motivo : 'Reclasificación administrativa manual',
                    'realizado_por' => auth()->id(),
                    'cambio_tpago' => 0,
                    'cambio_destino' => $cambioDestino ? 1 : 0,
                    'cambio_banco' => 0,
                    'moved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $mensaje = 'Pago reclasificado correctamente.';
        if (!Schema::connection('company')->hasTable('conciliacion_bancaria_movimientos')) {
            $mensaje .= ' Nota: no se registró Debe/Haber histórico porque falta la tabla conciliacion_bancaria_movimientos.';
        }

        return back()->with('success', $mensaje);
    }

    private function buildFilteredBaseQuery(Request $request)
    {
        $mysqlDb = config('database.connections.mysql.database');
        $vendedoresPorEmail = DB::connection('company')
            ->table('vendedores as vv')
            ->selectRaw('LOWER(TRIM(CONVERT(vv.email USING utf8mb4))) COLLATE utf8mb4_unicode_ci as email_norm')
            ->selectRaw('MIN(TRIM(CONVERT(vv.codigo USING utf8mb4))) as codigo')
            ->groupBy('email_norm');

        $query = DB::connection('company')->table('pagos as pag')
            ->leftJoin('TPAGO as tp', function ($join) {
                $join->whereRaw('TRIM(COALESCE(CONVERT(tp.CPAGO USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = TRIM(COALESCE(CONVERT(pag.tpago_id USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('BANCOS as bo', function ($join) {
                $join->whereRaw('TRIM(COALESCE(CONVERT(bo.CODIGO USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = TRIM(COALESCE(CONVERT(pag.banco_codigo USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('pago_destinos as pd', 'pd.id', '=', 'pag.pago_destino_id')
            ->leftJoin(DB::raw($mysqlDb . '.users as u'), 'u.id', '=', 'pag.user_id')
            ->leftJoinSub($vendedoresPorEmail, 'v', function ($join) {
                $join->whereRaw('LOWER(TRIM(CONVERT(u.email USING utf8mb4))) COLLATE utf8mb4_unicode_ci = v.email_norm');
            });

        if ($request->filled('fecha_desde')) {
            $query->whereDate('pag.fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('pag.fecha', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('vendedor_codigo')) {
            $query->whereRaw('TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")) = TRIM(COALESCE(CONVERT(? USING utf8mb4), ""))', [$request->input('vendedor_codigo')]);
        }

        if ($request->filled('tpago_id')) {
            $query->where('pag.tpago_id', $request->input('tpago_id'));
        }

        if ($request->filled('pago_destino_id')) {
            $query->where('pag.pago_destino_id', (int) $request->input('pago_destino_id'));
        }

        if ($request->filled('banco_codigo')) {
            $selection = self::parseBancoDestinoSelection($request->input('banco_codigo'));

            if ($selection['type'] === 'destino') {
                $query->where('pag.pago_destino_id', (int) $selection['value']);
            } else {
                $selectedBanco = trim((string) $selection['value']);
                $normalizedBanco = self::normalizeFilterValue($selectedBanco);

                $query->where(function ($q) use ($selectedBanco, $normalizedBanco) {
                    $q->where('pag.banco_codigo', $selectedBanco)
                        ->orWhereRaw('TRIM(COALESCE(CONVERT(bo.NOMBRE USING utf8mb4), "")) = ?', [$selectedBanco])
                        ->orWhereRaw('TRIM(COALESCE(CONVERT(pd.nombre USING utf8mb4), "")) = ?', [$selectedBanco])
                        ->orWhereRaw('REPLACE(REPLACE(LOWER(TRIM(COALESCE(CONVERT(bo.NOMBRE USING utf8mb4), ""))), " ", ""), "$", "") = ?', [$normalizedBanco])
                        ->orWhereRaw('REPLACE(REPLACE(LOWER(TRIM(COALESCE(CONVERT(pd.nombre USING utf8mb4), ""))), " ", ""), "$", "") = ?', [$normalizedBanco]);
                });
            }
        }

        if ($request->filled('estatus')) {
            $query->whereRaw('UPPER(COALESCE(pag.estatus, "")) = ?', [strtoupper((string) $request->input('estatus'))]);
        }

        if ($request->filled('moneda_pago')) {
            $query->whereRaw('LOWER(COALESCE(pag.moneda_pago, "")) = ?', [strtolower((string) $request->input('moneda_pago'))]);
        }

        if ($request->filled('pedido_id')) {
            $pedidoId = $request->input('pedido_id');
            $query->whereExists(function ($sq) use ($pedidoId) {
                $sq->select(DB::raw(1))
                    ->from('pagos_pedidos as ppf')
                    ->whereColumn('ppf.pago_id', 'pag.id')
                    ->where('ppf.pedido_id', $pedidoId);
            });
        }

        if ($request->filled('factura')) {
            $factura = trim((string) $request->input('factura'));
            $query->whereExists(function ($sq) use ($factura) {
                $sq->select(DB::raw(1))
                    ->from('pagos_pedidos as ppf')
                    ->join('pedidos_facturas as pff', 'pff.pedido_id', '=', 'ppf.pedido_id')
                    ->whereColumn('ppf.pago_id', 'pag.id')
                    ->whereRaw('CONVERT(COALESCE(pff.factura, "") USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $factura . '%']);
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CONVERT(COALESCE(pag.referencia, "") USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereExists(function ($sq) use ($search) {
                        $sq->select(DB::raw(1))
                            ->from('pagos_pedidos as pps')
                            ->leftJoin('pedidos as peds', 'peds.id', '=', 'pps.pedido_id')
                            ->leftJoin('pedidos_facturas as pfs', 'pfs.pedido_id', '=', 'peds.id')
                            ->whereColumn('pps.pago_id', 'pag.id')
                            ->where(function ($sx) use ($search) {
                                $sx->whereRaw('CAST(pps.pedido_id AS CHAR) LIKE ?', ['%' . $search . '%'])
                                    ->orWhereRaw('CONVERT(COALESCE(peds.descripcion, "") USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                                    ->orWhereRaw('CONVERT(COALESCE(pfs.factura, "") USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%']);
                            });
                    });
            });
        }

        return $query;
    }

    public static function normalizeFilterValue($value)
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

    public static function parseBancoDestinoSelection($value)
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return ['type' => 'none', 'value' => null];
        }

        if (preg_match('/^destino:(\d+)$/', $raw, $matches)) {
            return ['type' => 'destino', 'value' => (int) $matches[1]];
        }

        if (preg_match('/^banco:(.+)$/', $raw, $matches)) {
            return ['type' => 'banco', 'value' => trim($matches[1])];
        }

        return ['type' => 'banco', 'value' => $raw];
    }

    private function buildBancoDestinoFilterOptions($bancos, $destinos)
    {
        $items = collect();

        foreach ($bancos as $banco) {
            $items->push([
                'value' => 'banco:' . (string) $banco->CODIGO,
                'label' => (string) $banco->NOMBRE,
                'tipo' => 'banco',
            ]);
        }

        foreach ($destinos as $destino) {
            $items->push([
                'value' => 'destino:' . (string) $destino->id,
                'label' => (string) $destino->nombre,
                'tipo' => 'destino',
            ]);
        }

        return $items
            ->sortBy(function ($item) {
                return mb_strtolower($item['label'], 'UTF-8');
            })
            ->values()
            ->all();
    }

    private function buildDetallePagosSubquery()
    {
        return DB::connection('company')
            ->table('pagos_pedidos as pp')
            ->leftJoin('pedidos as ped', 'ped.id', '=', 'pp.pedido_id')
            ->leftJoin('pedidos_facturas as pf', 'pf.pedido_id', '=', 'ped.id')
            ->leftJoin('pagos as pag2', 'pag2.id', '=', 'pp.pago_id')
            ->selectRaw('pp.pago_id')
            ->selectRaw('COALESCE(SUM(CASE
                WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(CONVERT(pag2.moneda_pago USING utf8mb4), "")), "í", "i"), "á", "a")) IN ("bolivares", "bs")
                    THEN 0
                ELSE COALESCE(pp.monto, 0)
            END), 0) as monto_usd')
            ->selectRaw('COALESCE(SUM(CASE
                WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(CONVERT(pag2.moneda_pago USING utf8mb4), "")), "í", "i"), "á", "a")) IN ("bolivares", "bs")
                    THEN COALESCE((COALESCE(pp.monto, 0) * COALESCE(pag2.rate, 0)) + COALESCE(pp.iva, 0) + (COALESCE(pp.ajustes_monto, 0) * COALESCE(pag2.rate, 0)), 0)
                ELSE 0
            END), 0) as monto_bs')
            ->selectRaw('GROUP_CONCAT(DISTINCT pp.pedido_id ORDER BY pp.pedido_id ASC SEPARATOR ", ") as pedidos')
            ->selectRaw('GROUP_CONCAT(DISTINCT pf.factura ORDER BY pf.factura ASC SEPARATOR ", ") as facturas')
            ->selectRaw('GROUP_CONCAT(DISTINCT ped.descripcion ORDER BY ped.descripcion ASC SEPARATOR " | ") as clientes')
            ->groupBy('pp.pago_id');
    }

    private function buildBalanceDestinoTipo(Request $request, $baseQuery, $tablaMovimientosDisponible)
    {
        $detallePagos = $this->buildDetallePagosSubquery();

        $balanceRows = (clone $baseQuery)
            ->leftJoinSub($detallePagos, 'dp', function ($join) {
                $join->on('dp.pago_id', '=', 'pag.id');
            })
            ->selectRaw('COALESCE(pd.id, 0) as destino_id')
            ->selectRaw('COALESCE(pd.nombre, "SIN DESTINO") as destino_nombre')
            ->selectRaw('COALESCE(tp.CPAGO, "") as tpago_id')
            ->selectRaw('COALESCE(tp.DPAGO, "SIN TIPO") as tpago_nombre')
            ->selectRaw('COALESCE(SUM(dp.monto_usd), 0) as haber_usd')
            ->selectRaw('COALESCE(SUM(dp.monto_bs), 0) as haber_bs')
            ->groupBy('pd.id', 'pd.nombre', 'tp.CPAGO', 'tp.DPAGO')
            ->orderBy('pd.nombre')
            ->orderBy('tp.DPAGO')
            ->get();

        $items = [];
        foreach ($balanceRows as $row) {
            $key = $row->destino_id . '|' . $row->tpago_id;
            $items[$key] = [
                'destino_id' => (int) $row->destino_id,
                'destino_nombre' => (string) $row->destino_nombre,
                'tpago_id' => (string) $row->tpago_id,
                'tpago_nombre' => (string) $row->tpago_nombre,
                'debe_usd' => 0,
                'debe_bs' => 0,
                'haber_usd' => (float) $row->haber_usd,
                'haber_bs' => (float) $row->haber_bs,
            ];
        }

        if ($tablaMovimientosDisponible) {
            $idsSubquery = (clone $baseQuery)
                ->select('pag.id')
                ->distinct();

            $movBase = DB::connection('company')
                ->table('conciliacion_bancaria_movimientos as m')
                ->whereIn('m.pago_id', $idsSubquery);

            $debeRows = (clone $movBase)
                ->selectRaw('COALESCE(m.origen_destino_id, 0) as destino_id')
                ->selectRaw('COALESCE(m.origen_tpago_id, "") as tpago_id')
                ->selectRaw('COALESCE(SUM(m.monto_usd), 0) as debe_usd')
                ->selectRaw('COALESCE(SUM(m.monto_bs), 0) as debe_bs')
                ->groupBy('m.origen_destino_id', 'm.origen_tpago_id')
                ->get();

            $haberRows = (clone $movBase)
                ->selectRaw('COALESCE(m.destino_destino_id, 0) as destino_id')
                ->selectRaw('COALESCE(m.destino_tpago_id, "") as tpago_id')
                ->selectRaw('COALESCE(SUM(m.monto_usd), 0) as haber_mov_usd')
                ->selectRaw('COALESCE(SUM(m.monto_bs), 0) as haber_mov_bs')
                ->groupBy('m.destino_destino_id', 'm.destino_tpago_id')
                ->get();

            foreach ($debeRows as $row) {
                $key = ((int) $row->destino_id) . '|' . ((string) $row->tpago_id);
                if (!isset($items[$key])) {
                    $items[$key] = [
                        'destino_id' => (int) $row->destino_id,
                        'destino_nombre' => 'SIN DESTINO',
                        'tpago_id' => (string) $row->tpago_id,
                        'tpago_nombre' => 'SIN TIPO',
                        'debe_usd' => 0,
                        'debe_bs' => 0,
                        'haber_usd' => 0,
                        'haber_bs' => 0,
                    ];
                }
                $items[$key]['debe_usd'] += (float) $row->debe_usd;
                $items[$key]['debe_bs'] += (float) $row->debe_bs;
            }

            foreach ($haberRows as $row) {
                $key = ((int) $row->destino_id) . '|' . ((string) $row->tpago_id);
                if (!isset($items[$key])) {
                    $items[$key] = [
                        'destino_id' => (int) $row->destino_id,
                        'destino_nombre' => 'SIN DESTINO',
                        'tpago_id' => (string) $row->tpago_id,
                        'tpago_nombre' => 'SIN TIPO',
                        'debe_usd' => 0,
                        'debe_bs' => 0,
                        'haber_usd' => 0,
                        'haber_bs' => 0,
                    ];
                }
                $items[$key]['haber_usd'] += (float) $row->haber_mov_usd;
                $items[$key]['haber_bs'] += (float) $row->haber_mov_bs;
            }

            $destinos = PagoDestino::pluck('nombre', 'id')->toArray();
            $tipos = OrderTpago::pluck('DPAGO', 'CPAGO')->toArray();

            foreach ($items as $k => $item) {
                if ($item['destino_nombre'] === 'SIN DESTINO' && isset($destinos[$item['destino_id']])) {
                    $items[$k]['destino_nombre'] = $destinos[$item['destino_id']];
                }
                if ($item['tpago_nombre'] === 'SIN TIPO' && isset($tipos[$item['tpago_id']])) {
                    $items[$k]['tpago_nombre'] = $tipos[$item['tpago_id']];
                }
            }
        }

        $result = collect(array_values($items))->map(function ($item) {
            $item['saldo_usd'] = (float) $item['haber_usd'] - (float) $item['debe_usd'];
            $item['saldo_bs'] = (float) $item['haber_bs'] - (float) $item['debe_bs'];
            return (object) $item;
        })->sortBy([
            ['destino_nombre', 'asc'],
            ['tpago_nombre', 'asc'],
        ])->values();

        return $result;
    }

    private function getVendedores()
    {
        $mysqlDb = config('database.connections.mysql.database');

        return DB::connection('company')
            ->table('vendedores as v')
            ->leftJoin(DB::raw($mysqlDb . '.users as u'), function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->selectRaw('COALESCE(v.codigo, "") as codigo')
            ->selectRaw('COALESCE(u.name, v.codigo, v.email) as nombre')
            ->whereRaw('TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")) <> ""')
            ->groupBy('v.codigo', 'u.name', 'v.email')
            ->orderBy('v.codigo')
            ->get();
    }
}
