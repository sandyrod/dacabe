<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteVendedor360Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function exportExcel(Request $request)
    {
        [$vendedores, $vendedorSeleccionado, $filtros] = $this->resolverContexto($request);
        if (!$vendedorSeleccionado) {
            return redirect()->route('admin.reportes.vendedor.360')
                ->with('error', 'No se encontro vendedor para exportar.');
        }

        $data = $this->buildData($vendedorSeleccionado, $filtros);
        $filename = 'vision_360_vendedor_' . $vendedorSeleccionado->codigo . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\Vendedor360PedidosExport($data['pedidosExport']), $filename);
    }

    public function exportPdf(Request $request)
    {
        [$vendedores, $vendedorSeleccionado, $filtros] = $this->resolverContexto($request);
        if (!$vendedorSeleccionado) {
            return redirect()->route('admin.reportes.vendedor.360')
                ->with('error', 'No se encontro vendedor para exportar.');
        }

        $data = $this->buildData($vendedorSeleccionado, $filtros);
        $pdf = Pdf::loadView('admin.reportes.vendedor_360_pdf', [
            'vendedorSeleccionado' => $vendedorSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'topProductos' => $data['topProductos'],
            'pedidosExport' => $data['pedidosExport'],
            'carteraDetalle' => $data['carteraDetalle'],
        ]);

        return $pdf->stream('vision_360_vendedor_' . $vendedorSeleccionado->codigo . '.pdf');
    }

    public function index(Request $request)
    {
        [$vendedores, $vendedorSeleccionado, $filtros] = $this->resolverContexto($request);

        if (!$vendedorSeleccionado) {
            return view('admin.reportes.vendedor_360', [
                'vendedores' => $vendedores,
                'vendedorSeleccionado' => null,
                'filtros' => $filtros,
                'resumen' => [
                    'pedidos_total' => 0,
                    'pedidos_aprobados' => 0,
                    'pedidos_pendientes' => 0,
                    'ventas_usd' => 0,
                    'unidades' => 0,
                    'ticket_promedio' => 0,
                    'pagos_aprobados' => 0,
                    'pagos_revision' => 0,
                    'saldo_cartera_usd' => 0,
                    'saldo_cartera_bs' => 0,
                    'saldo_base_usd' => 0,
                    'saldo_iva_bs' => 0,
                    'saldo_ajustes_usd' => 0,
                    'pedidos_vencidos' => 0,
                    'pedidos_por_vencer' => 0,
                    'cobranza_vs_venta' => 0,
                ],
                'estadoPedidos' => collect(),
                'ventasMensuales' => collect(),
                'topProductos' => collect(),
                'ultimosPedidos' => collect(),
                'carteraDetalle' => collect(),
            ]);
        }

        $data = $this->buildData($vendedorSeleccionado, $filtros);

        return view('admin.reportes.vendedor_360', [
            'vendedores' => $vendedores,
            'vendedorSeleccionado' => $vendedorSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'estadoPedidos' => $data['estadoPedidos'],
            'ventasMensuales' => $data['ventasMensuales'],
            'topProductos' => $data['topProductos'],
            'ultimosPedidos' => $data['ultimosPedidos'],
            'carteraDetalle' => $data['carteraDetalle'],
        ]);
    }

    private function resolverContexto(Request $request): array
    {
        $vendedores = $this->getVendedores();

        $vendedorId = (int) $request->get('vendedor_id', 0);
        if ($vendedorId <= 0 && $vendedores->count() > 0) {
            $vendedorId = (int) $vendedores->first()->id;
        }

        $filtros = [
            'vendedor_id' => $vendedorId,
            'fecha_desde' => $request->filled('fecha_desde') ? Carbon::parse($request->fecha_desde)->format('Y-m-d') : null,
            'fecha_hasta' => $request->filled('fecha_hasta') ? Carbon::parse($request->fecha_hasta)->format('Y-m-d') : null,
        ];

        if ($filtros['fecha_desde'] && $filtros['fecha_hasta'] && $filtros['fecha_desde'] > $filtros['fecha_hasta']) {
            $tmp = $filtros['fecha_desde'];
            $filtros['fecha_desde'] = $filtros['fecha_hasta'];
            $filtros['fecha_hasta'] = $tmp;
        }

        $vendedorSeleccionado = $vendedores->firstWhere('id', $vendedorId);

        return [$vendedores, $vendedorSeleccionado, $filtros];
    }

    private function getVendedores()
    {
        $companyDb = DB::connection('company')->getDatabaseName();
        $mysqlDb = config('database.connections.mysql.database');

        return DB::connection('company')
            ->table($companyDb . '.vendedores as v')
            ->leftJoin($mysqlDb . '.users as u', function ($join) {
                $join->whereRaw('LOWER(TRIM(COALESCE(CONVERT(v.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(COALESCE(CONVERT(u.email USING utf8mb4), ""))) COLLATE utf8mb4_unicode_ci');
            })
            ->selectRaw('v.id')
            ->selectRaw('v.codigo')
            ->selectRaw('v.email')
            ->selectRaw('u.id as user_id')
            ->selectRaw('COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, ""))), ""), v.codigo, v.email) as nombre')
            ->orderBy('v.codigo')
            ->get();
    }

    private function buildData($vendedorSeleccionado, array $filtros): array
    {
        $sellerFilter = $this->sellerFilter($vendedorSeleccionado);
        $fechaDesde = $filtros['fecha_desde'];
        $fechaHasta = $filtros['fecha_hasta'];

        $pedidosBase = DB::connection('company')->table('pedidos as p')->where($sellerFilter);
        $this->applyPedidosDateRange($pedidosBase, $fechaDesde, $fechaHasta);

        $pedidosValidos = DB::connection('company')
            ->table('pedidos as p')
            ->where($sellerFilter)
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")');
        $this->applyPedidosDateRange($pedidosValidos, $fechaDesde, $fechaHasta);

        $resumenPedidos = (clone $pedidosBase)
            ->selectRaw('COUNT(*) as pedidos_total')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(p.estatus, "")) = "APROBADO" THEN 1 ELSE 0 END) as pedidos_aprobados')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(p.estatus, "")) = "PENDIENTE" THEN 1 ELSE 0 END) as pedidos_pendientes')
            ->first();

        $ventasGlobales = (clone $pedidosValidos)
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->first();

        $totalesPorPedido = DB::connection('company')
            ->table('pedido_detalle')
            ->selectRaw('pedido_id, COALESCE(SUM(cantidad * precio_dolar), 0) as total_usd, COALESCE(SUM(cantidad), 0) as total_unidades')
            ->groupBy('pedido_id');

        $ultimosPedidos = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoinSub($totalesPorPedido, 'tp', function ($join) {
                $join->on('tp.pedido_id', '=', 'p.id');
            })
            ->where($sellerFilter)
            ->selectRaw('p.id, p.fecha, p.descripcion, p.estatus, p.referencia, p.codcli')
            ->selectRaw('COALESCE(tp.total_usd, 0) as total_usd')
            ->selectRaw('COALESCE(tp.total_unidades, 0) as total_unidades');
        $this->applyPedidosDateRange($ultimosPedidos, $fechaDesde, $fechaHasta);
        $ultimosPedidos = $ultimosPedidos
            ->orderByDesc('p.fecha')
            ->orderByDesc('p.id')
            ->limit(20)
            ->get();

        $pedidosExport = DB::connection('company')
            ->table('pedidos as p')
            ->leftJoinSub($totalesPorPedido, 'tp', function ($join) {
                $join->on('tp.pedido_id', '=', 'p.id');
            })
            ->where($sellerFilter)
            ->selectRaw('p.id, p.fecha, p.referencia, p.descripcion, p.codcli, p.estatus')
            ->selectRaw('COALESCE(tp.total_unidades, 0) as total_unidades')
            ->selectRaw('COALESCE(tp.total_usd, 0) as total_usd');
        $this->applyPedidosDateRange($pedidosExport, $fechaDesde, $fechaHasta);
        $pedidosExport = $pedidosExport->orderByDesc('p.fecha')->orderByDesc('p.id')->get();

        $topProductos = DB::connection('company')
            ->table('pedidos as p')
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->where($sellerFilter)
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")')
            ->selectRaw('pd.codigo_inven')
            ->selectRaw('MAX(pd.inven_descr) as descripcion')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as total_usd')
            ->selectRaw('COUNT(DISTINCT p.id) as pedidos');
        $this->applyPedidosDateRange($topProductos, $fechaDesde, $fechaHasta);
        $topProductos = $topProductos
            ->groupBy('pd.codigo_inven')
            ->orderByDesc('unidades')
            ->limit(10)
            ->get();

        $estadoPedidos = (clone $pedidosBase)
            ->selectRaw('COALESCE(NULLIF(TRIM(p.estatus), ""), "SIN ESTATUS") as estatus')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('estatus')
            ->orderByDesc('cantidad')
            ->get();

        $inicioMes = Carbon::now()->startOfMonth()->subMonths(5);
        if ($fechaDesde) {
            $inicioMes = Carbon::parse($fechaDesde)->startOfMonth();
            $fin = $fechaHasta ? Carbon::parse($fechaHasta)->startOfMonth() : Carbon::now()->startOfMonth();
            $diff = $inicioMes->diffInMonths($fin);
            if ($diff > 5) {
                $inicioMes = (clone $fin)->subMonths(5);
            }
        }

        $ventasMensualesRaw = DB::connection('company')
            ->table('pedidos as p')
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->where($sellerFilter)
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")')
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $inicioMes->toDateString())
            ->selectRaw('DATE_FORMAT(COALESCE(p.fecha, p.created_at), "%Y-%m") as periodo')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd');
        $this->applyPedidosDateRange($ventasMensualesRaw, $fechaDesde, $fechaHasta);
        $ventasMensualesRaw = $ventasMensualesRaw
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $ventasMensuales = collect();
        for ($i = 0; $i < 6; $i++) {
            $mes = (clone $inicioMes)->addMonths($i);
            $key = $mes->format('Y-m');
            $ventasMensuales->push((object) [
                'periodo' => $key,
                'etiqueta' => ucfirst($mes->locale('es')->translatedFormat('M Y')),
                'ventas_usd' => (float) optional($ventasMensualesRaw->get($key))->ventas_usd,
            ]);
        }

        $pagosResumen = DB::connection('company')
            ->table('pagos as pg')
            ->where('pg.seller_id', $vendedorSeleccionado->id)
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "APROBADO" THEN pg.monto ELSE 0 END), 0) as pagos_aprobados')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "EN REVISION" THEN pg.monto ELSE 0 END), 0) as pagos_revision');
        if ($fechaDesde) {
            $pagosResumen->whereDate('pg.fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $pagosResumen->whereDate('pg.fecha', '<=', $fechaHasta);
        }
        $pagosResumen = $pagosResumen->first();

        $tasaDelDia = (float) (optional(Tasa::whereDate('fecha', Carbon::today()->toDateString())->first())->valor ?? 0);
        $tasaSql = $tasaDelDia > 0 ? $tasaDelDia : 0;

        $exprSaldoBaseBs = 'COALESCE(p.saldo_base, 0) * ' . $tasaSql;
        $exprSaldoAjustesBs = 'COALESCE(p.saldo_ajustes, 0) * ' . $tasaSql;
        $exprSaldoTotalBs = '(' . $exprSaldoBaseBs . ' + COALESCE(p.saldo_iva_bs, 0) + ' . $exprSaldoAjustesBs . ')';
        $exprSaldoTotalUsd = $tasaSql > 0
            ? '(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_ajustes, 0) + (COALESCE(p.saldo_iva_bs, 0) / ' . $tasaSql . '))'
            : '(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_ajustes, 0))';

        $carteraBase = DB::connection('company')
            ->table('pedidos as p')
            ->where($sellerFilter)
            ->whereRaw('BINARY p.estatus NOT IN (BINARY ?, BINARY ?)', ['CANCELADO', 'PAGADO'])
            ->whereRaw('(COALESCE(p.saldo_base, 0) + COALESCE(p.saldo_iva_bs, 0) + COALESCE(p.saldo_ajustes, 0)) > 0.01')
            ->whereNotNull('p.fecha_despacho')
            ->whereRaw('TRIM(COALESCE(p.fecha_despacho, "")) != ""');
        $this->applyPedidosDateRange($carteraBase, $fechaDesde, $fechaHasta);

        $resumenCartera = (clone $carteraBase)
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_base, 0)), 0) as total_saldo_base_usd')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_iva_bs, 0)), 0) as total_saldo_iva_bs')
            ->selectRaw('COALESCE(SUM(COALESCE(p.saldo_ajustes, 0)), 0) as total_saldo_ajustes_usd')
            ->selectRaw('COALESCE(SUM(' . $exprSaldoTotalUsd . '), 0) as total_cartera_usd')
            ->selectRaw('COALESCE(SUM(' . $exprSaldoTotalBs . '), 0) as total_cartera_bs')
            ->first();

        $pedidosVencidos = (clone $carteraBase)
            ->where('p.dias_credito', '>', 0)
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) < CURDATE()')
            ->count();

        $pedidosPorVencer = (clone $carteraBase)
            ->where('p.dias_credito', '>', 0)
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) >= CURDATE()')
            ->whereRaw('DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)')
            ->count();

        $carteraDetalle = (clone $carteraBase)
            ->selectRaw('p.id, p.fecha, p.fecha_despacho, p.descripcion, p.referencia, p.codcli, p.estatus, p.dias_credito')
            ->selectRaw('COALESCE(p.saldo_base, 0) as saldo_base_usd')
            ->selectRaw('COALESCE(p.saldo_iva_bs, 0) as saldo_iva_bs')
            ->selectRaw('COALESCE(p.saldo_ajustes, 0) as saldo_ajustes_usd')
            ->selectRaw($exprSaldoTotalUsd . ' as saldo_total_usd')
            ->selectRaw($exprSaldoTotalBs . ' as saldo_total_bs')
            ->selectRaw('CASE
                WHEN COALESCE(p.dias_credito, 0) > 0
                THEN DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY)
                ELSE NULL
            END as fecha_vencimiento')
            ->selectRaw('CASE
                WHEN COALESCE(p.dias_credito, 0) > 0
                THEN DATEDIFF(DATE_ADD(p.fecha_despacho, INTERVAL p.dias_credito DAY), CURDATE())
                ELSE NULL
            END as dias_restantes')
            ->orderBy('p.fecha', 'asc')
            ->orderBy('p.id', 'asc')
            ->limit(15)
            ->get();

        $ventasUsd = (float) ($ventasGlobales->ventas_usd ?? 0);
        $pedidosTotales = (int) ($resumenPedidos->pedidos_total ?? 0);
        $pagosAprobados = (float) ($pagosResumen->pagos_aprobados ?? 0);

        return [
            'resumen' => [
                'pedidos_total' => $pedidosTotales,
                'pedidos_aprobados' => (int) ($resumenPedidos->pedidos_aprobados ?? 0),
                'pedidos_pendientes' => (int) ($resumenPedidos->pedidos_pendientes ?? 0),
                'ventas_usd' => $ventasUsd,
                'unidades' => (float) ($ventasGlobales->unidades ?? 0),
                'ticket_promedio' => $pedidosTotales > 0 ? ($ventasUsd / $pedidosTotales) : 0,
                'pagos_aprobados' => $pagosAprobados,
                'pagos_revision' => (float) ($pagosResumen->pagos_revision ?? 0),
                'saldo_cartera_usd' => (float) ($resumenCartera->total_cartera_usd ?? 0),
                'saldo_cartera_bs' => (float) ($resumenCartera->total_cartera_bs ?? 0),
                'saldo_base_usd' => (float) ($resumenCartera->total_saldo_base_usd ?? 0),
                'saldo_iva_bs' => (float) ($resumenCartera->total_saldo_iva_bs ?? 0),
                'saldo_ajustes_usd' => (float) ($resumenCartera->total_saldo_ajustes_usd ?? 0),
                'pedidos_vencidos' => (int) $pedidosVencidos,
                'pedidos_por_vencer' => (int) $pedidosPorVencer,
                'cobranza_vs_venta' => $ventasUsd > 0 ? (($pagosAprobados / $ventasUsd) * 100) : 0,
            ],
            'estadoPedidos' => $estadoPedidos,
            'ventasMensuales' => $ventasMensuales,
            'topProductos' => $topProductos,
            'ultimosPedidos' => $ultimosPedidos,
            'pedidosExport' => $pedidosExport,
            'carteraDetalle' => $carteraDetalle,
        ];
    }

    private function applyPedidosDateRange($query, ?string $fechaDesde, ?string $fechaHasta): void
    {
        if ($fechaDesde) {
            $query->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '<=', $fechaHasta);
        }
    }

    private function sellerFilter($vendedorSeleccionado): \Closure
    {
        return function ($q) use ($vendedorSeleccionado) {
            $hasUserId = !empty($vendedorSeleccionado->user_id);
            $hasCode = !empty($vendedorSeleccionado->codigo);

            if (!$hasUserId && !$hasCode) {
                $q->whereRaw('1 = 0');
                return;
            }

            $q->where(function ($w) use ($vendedorSeleccionado, $hasUserId, $hasCode) {
                if ($hasUserId) {
                    $w->where('p.user_id', $vendedorSeleccionado->user_id);
                }
                if ($hasCode) {
                    $method = $hasUserId ? 'orWhere' : 'where';
                    $w->{$method}('p.seller_code', $vendedorSeleccionado->codigo);
                }
            });
        };
    }
}
