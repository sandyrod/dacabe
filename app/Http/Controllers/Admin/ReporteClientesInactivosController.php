<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteClientesInactivosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyDb = DB::connection('company')->getDatabaseName();
        $mysqlDb = config('database.connections.mysql.database');
        $today = Carbon::today();

        $rifClienteExpr = 'TRIM(COALESCE(CONVERT(c.RIF USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci';
        $rifPedidoExpr = 'TRIM(COALESCE(CONVERT(p.rif USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci';
        $rifVendedorExpr = 'TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci';

        $pedidosUltimos = DB::connection('company')
            ->table($companyDb . '.pedidos as p')
            ->selectRaw($rifPedidoExpr . ' as rif_norm')
            ->selectRaw('MAX(p.fecha) as ultima_compra')
            ->selectRaw('COUNT(*) as total_pedidos')
            ->whereRaw('COALESCE(TRIM(COALESCE(CONVERT(p.rif USING utf8mb4), "")), "") <> ""')
            ->whereRaw('BINARY p.estatus NOT IN (BINARY ?, BINARY ?, BINARY ?)', ['ANULADO', 'CANCELADO', 'CARGANDO'])
            ->groupByRaw($rifPedidoExpr);

        $query = DB::connection('company')
            ->table($companyDb . '.CLIENTE as c')
            ->joinSub($pedidosUltimos, 'pu', function ($join) use ($rifClienteExpr) {
                $join->on(DB::raw($rifClienteExpr), '=', DB::raw('pu.rif_norm'));
            })
            ->leftJoin($companyDb . '.cliente_vendedor as cv', function ($join) use ($rifClienteExpr) {
                $join->whereRaw('TRIM(COALESCE(CONVERT(cv.rif USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = ' . $rifClienteExpr);
            })
            ->leftJoin($companyDb . '.vendedores as v', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(cv.email_vendedor USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin($mysqlDb . '.users as u', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->selectRaw('c.RIF as rif')
            ->selectRaw('c.NOMBRE as nombre')
            ->selectRaw('c.TELEFONO as telefono')
            ->selectRaw('c.EMAIL as email')
            ->selectRaw('c.CODCLI as codcli')
            ->selectRaw('c.CLIAGERET as cliageret')
            ->selectRaw('COALESCE(pu.ultima_compra, NULL) as ultima_compra')
            ->selectRaw('COALESCE(pu.total_pedidos, 0) as total_pedidos')
            ->selectRaw('CASE
                WHEN pu.ultima_compra IS NULL THEN NULL
                ELSE DATEDIFF(CURDATE(), pu.ultima_compra)
            END as dias_sin_comprar')
            ->selectRaw('COALESCE(u.name, v.codigo, "Sin asignar") as vendedor_nombre')
            ->selectRaw('COALESCE(v.codigo, "") as vendedor_codigo')
            ->selectRaw('COALESCE(v.email, "") as vendedor_email')
            ->selectRaw('CASE WHEN pu.ultima_compra IS NULL THEN 1 ELSE 0 END as sin_compra')
            ->whereRaw('TRIM(COALESCE(CONVERT(c.RIF USING utf8mb4), "")) <> ""');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CONVERT(c.NOMBRE USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(c.RIF USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(c.TELEFONO USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(c.EMAIL USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(v.codigo USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%'])
                    ->orWhereRaw('CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_unicode_ci LIKE CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci', ['%' . $search . '%']);
            });
        }

        if ($request->filled('vendedor')) {
            $vendedor = trim((string) $request->vendedor);
            $query->where(function ($q) use ($vendedor) {
                $q->whereRaw('TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci = TRIM(COALESCE(CONVERT(? USING utf8mb4), "")) COLLATE utf8mb4_unicode_ci', [$vendedor])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(CONVERT(u.name USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci LIKE LOWER(CONVERT(? USING utf8mb4)) COLLATE utf8mb4_unicode_ci', ['%' . $vendedor . '%']);
            });
        }

        if ($request->filled('min_days')) {
            $query->whereRaw('COALESCE(CASE WHEN pu.ultima_compra IS NULL THEN 99999 ELSE DATEDIFF(CURDATE(), pu.ultima_compra) END, 99999) >= ?', [(int) $request->min_days]);
        }

        if ($request->filled('max_days')) {
            $query->whereRaw('COALESCE(CASE WHEN pu.ultima_compra IS NULL THEN 99999 ELSE DATEDIFF(CURDATE(), pu.ultima_compra) END, 99999) <= ?', [(int) $request->max_days]);
        }

        if ($request->boolean('sin_compra')) {
            $query->whereNull('pu.ultima_compra');
        }

        if ($request->boolean('sin_vendedor')) {
            $query->whereRaw('COALESCE(TRIM(COALESCE(CONVERT(v.codigo USING utf8mb4), "")), "") = ""');
        }

        $statsBase = clone $query;

        $resumen = DB::query()
            ->fromSub($statsBase, 'base')
            ->selectRaw('COUNT(*) as total_clientes')
            ->selectRaw('SUM(CASE WHEN base.ultima_compra IS NULL THEN 1 ELSE 0 END) as clientes_sin_compra')
            ->selectRaw('SUM(CASE WHEN base.ultima_compra IS NOT NULL AND DATEDIFF(CURDATE(), base.ultima_compra) >= 30 THEN 1 ELSE 0 END) as mas_30_dias')
            ->selectRaw('SUM(CASE WHEN base.ultima_compra IS NOT NULL AND DATEDIFF(CURDATE(), base.ultima_compra) >= 90 THEN 1 ELSE 0 END) as mas_90_dias')
            ->selectRaw('SUM(CASE WHEN base.ultima_compra IS NOT NULL AND DATEDIFF(CURDATE(), base.ultima_compra) >= 180 THEN 1 ELSE 0 END) as mas_180_dias')
            ->selectRaw('COALESCE(AVG(CASE WHEN base.ultima_compra IS NULL THEN NULL ELSE DATEDIFF(CURDATE(), base.ultima_compra) END), 0) as promedio_dias')
            ->selectRaw('MAX(CASE WHEN base.ultima_compra IS NULL THEN NULL ELSE DATEDIFF(CURDATE(), base.ultima_compra) END) as maximo_dias')
            ->selectRaw('SUM(CASE WHEN base.total_pedidos = 1 THEN 1 ELSE 0 END) as clientes_una_compra')
            ->first();

        $clientesPrioridad = (clone $statsBase)
            ->orderByRaw('COALESCE(CASE WHEN pu.ultima_compra IS NULL THEN 99999 ELSE DATEDIFF(CURDATE(), pu.ultima_compra) END, 99999) DESC')
            ->orderBy('c.NOMBRE')
            ->paginate(20);

        $clientesPrioridad->appends($request->query());

        $topInactivos = DB::query()
            ->fromSub($statsBase, 'base')
            ->orderByRaw('COALESCE(CASE WHEN base.ultima_compra IS NULL THEN 99999 ELSE DATEDIFF(CURDATE(), base.ultima_compra) END, 99999) DESC')
            ->limit(8)
            ->get();

        $vendedores = DB::query()
            ->fromSub($statsBase, 'base')
            ->selectRaw('base.vendedor_codigo as codigo')
            ->selectRaw('base.vendedor_nombre as nombre')
            ->whereRaw('TRIM(COALESCE(base.vendedor_codigo, "")) <> ""')
            ->groupBy('base.vendedor_codigo', 'base.vendedor_nombre')
            ->orderBy('base.vendedor_codigo')
            ->get();

        $diasObjetivo = [30, 60, 90, 180];

        return view('admin.reportes.clientes_inactivos', compact(
            'clientesPrioridad',
            'resumen',
            'topInactivos',
            'vendedores',
            'diasObjetivo',
            'today'
        ));
    }
}
