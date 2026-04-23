<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentasPorCobrarController extends Controller
{
    public function index(Request $request)
    {
        $companyDb = DB::connection('company')->getDatabaseName();
        $mysqlDb = config('database.connections.mysql.database');
        $tasaDelDia = (float) (optional(Tasa::whereDate('fecha', Carbon::today()->toDateString())->first())->valor ?? 0);

        $baseQuery = DB::connection('company')
            ->table(DB::raw($companyDb . '.pedidos as p'))
            ->leftJoin(DB::raw($companyDb . '.vendedores as v'), function ($join) {
                $join->whereRaw('TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin(DB::raw($mysqlDb . '.users as u'), function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin(
                DB::raw('(SELECT pedido_id, MAX(factura) as factura_numero FROM ' . $companyDb . '.pedidos_facturas GROUP BY pedido_id) as pf'),
                'pf.pedido_id',
                '=',
                'p.id'
            )
            ->whereRaw('BINARY p.estatus NOT IN (BINARY ?, BINARY ?)', ['CANCELADO', 'PAGADO'])
            ->whereRaw('(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_iva_bs, 0) + COALESCE(p.saldo_ajustes, 0)) > 0.01')
            ->whereNotNull('p.fecha_despacho')
            ->whereRaw('TRIM(COALESCE(p.fecha_despacho, "")) != ""');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->whereRaw('CAST(p.id AS CHAR) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(p.descripcion USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(p.rif USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(p.referencia USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(p.seller_code USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%']);
            });
        }

        if ($request->has('vendedor')) {
            $codigoVendedor = trim((string) $request->input('vendedor'));
            if ($codigoVendedor !== '') {
                $baseQuery->whereRaw(
                    'TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = TRIM(COALESCE(CONVERT(? USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci',
                    [$codigoVendedor]
                );
            }
        }

        if ($request->filled('estatus')) {
            $baseQuery->whereRaw('BINARY p.estatus = BINARY ?', [$request->estatus]);
        }

        if ($request->filled('desde')) {
            $baseQuery->whereDate('p.fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $baseQuery->whereDate('p.fecha', '<=', $request->hasta);
        }

        if ($request->boolean('solo_vencidos')) {
            $baseQuery->where('p.dias_credito', '>', 0)
                ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) < CURDATE()');
        }

        $statsBase = clone $baseQuery;

        $resumen = (clone $statsBase)
            ->selectRaw('COUNT(*) as total_pedidos')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_base, 0)), 0) as total_saldo_base')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_iva_bs, 0)), 0) as total_saldo_iva')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_ajustes, 0)), 0) as total_saldo_ajustes')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_iva_bs, 0) + COALESCE(p.saldo_ajustes, 0)), 0) as total_cartera')
            ->first();

        $vencidosCount = (clone $statsBase)
            ->where('p.dias_credito', '>', 0)
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) < CURDATE()')
            ->count();

        $hoyCount = (clone $statsBase)
            ->whereDate('p.fecha', Carbon::today()->toDateString())
            ->count();

        $proxVencerCount = (clone $statsBase)
            ->where('p.dias_credito', '>', 0)
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) >= CURDATE()')
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)')
            ->count();

        $diasVencidosPromedio = (clone $statsBase)
            ->where('p.dias_credito', '>', 0)
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) < CURDATE()')
            ->selectRaw('COALESCE(AVG(DATEDIFF(CURDATE(), DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY))), 0) as promedio')
            ->value('promedio');

        $antiguedad = (clone $statsBase)
            ->selectRaw('COALESCE(AVG(DATEDIFF(CURDATE(), p.fecha)), 0) as dias_promedio')
            ->selectRaw('COALESCE(MAX(DATEDIFF(CURDATE(), p.fecha)), 0) as dias_maximos')
            ->first();

        $topVendedores = (clone $statsBase)
            ->selectRaw("CASE
                WHEN p.seller_code IS NULL OR CHAR_LENGTH(TRIM(p.seller_code)) = 0
                THEN _utf8mb4'SIN COD' COLLATE utf8mb4_unicode_ci
                ELSE CONVERT(p.seller_code USING utf8mb4) COLLATE utf8mb4_unicode_ci
            END as codigo_vendedor")
            ->selectRaw('COALESCE(u.name, "Sin asignar") as vendedor_nombre')
            ->selectRaw('COUNT(*) as pedidos_pendientes')
            ->selectRaw('SUM(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_iva_bs, 0) + COALESCE(p.saldo_ajustes, 0)) as saldo_total')
            ->groupByRaw("CASE
                WHEN p.seller_code IS NULL OR CHAR_LENGTH(TRIM(p.seller_code)) = 0
                THEN _utf8mb4'SIN COD' COLLATE utf8mb4_unicode_ci
                ELSE CONVERT(p.seller_code USING utf8mb4) COLLATE utf8mb4_unicode_ci
            END")
            ->groupByRaw('COALESCE(u.name, "Sin asignar")')
            ->orderByDesc('saldo_total')
            ->limit(5)
            ->get();

        $pedidos = (clone $baseQuery)
            ->select(
                'p.id',
                'p.fecha',
                'p.fecha_despacho',
                'p.referencia',
                'p.descripcion',
                'p.rif',
                'p.telefono',
                'p.email',
                'p.seller_code',
                'p.estatus',
                'p.factura',
                'p.dias_credito',
                'p.base',
                'p.iva_bs',
                'p.porc_retencion',
                'p.total_ajustes',
                'p.saldo_base',
                'p.saldo_iva_bs',
                'p.saldo_ajustes',
                DB::raw('pf.factura_numero as numero_factura'),
                DB::raw('COALESCE(u.name, "Sin asignar") as vendedor_nombre'),
                DB::raw('COALESCE(v.email, "") as vendedor_email'),
                DB::raw('"N/A" as deposito_nombre'),
                DB::raw('(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_iva_bs, 0) + COALESCE(p.saldo_ajustes, 0)) as saldo_total'),
                DB::raw('CASE
                    WHEN COALESCE(p.dias_credito, 0) > 0
                    THEN DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY)
                    ELSE NULL
                END as fecha_vencimiento'),
                DB::raw('DATEDIFF(CURDATE(), p.fecha) as antiguedad_dias'),
                DB::raw('CASE
                    WHEN COALESCE(p.dias_credito, 0) > 0
                    THEN DATEDIFF(DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY), CURDATE())
                    ELSE NULL
                END as dias_restantes'),
                DB::raw('CASE
                    WHEN COALESCE(p.dias_credito, 0) > 0 AND DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) < CURDATE()
                    THEN DATEDIFF(CURDATE(), DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY))
                    ELSE 0
                END as dias_vencidos')
            )
            ->orderBy('p.fecha', 'asc')
            ->orderBy('p.id', 'asc')
            ->paginate(25);

        $pedidos->appends($request->query());

        $vendedores = (clone $statsBase)
            ->selectRaw('TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), "")) as codigo')
            ->selectRaw('COALESCE(u.name, "Sin asignar") as nombre')
            ->whereRaw('TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), "")) <> ""')
            ->groupByRaw('TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), ""))')
            ->groupByRaw('COALESCE(u.name, "Sin asignar")')
            ->orderByRaw('TRIM(COALESCE(CONVERT(p.seller_code USING utf8mb4), ""))')
            ->get();

        $estatuses = DB::connection('company')
            ->table('pedidos')
            ->whereNotNull('estatus')
            ->whereRaw('TRIM(estatus) <> ""')
            ->distinct()
            ->orderBy('estatus')
            ->pluck('estatus');

        return view('admin.cuentas_por_cobrar.index', compact(
            'pedidos',
            'resumen',
            'vencidosCount',
            'hoyCount',
            'antiguedad',
            'topVendedores',
            'vendedores',
            'estatuses',
            'tasaDelDia',
            'proxVencerCount',
            'diasVencidosPromedio'
        ));
    }
}
