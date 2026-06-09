<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tasa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteCliente360Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        [$clientes, $clienteSeleccionado, $filtros] = $this->resolverContexto($request);

        if (!$clienteSeleccionado) {
            return view('admin.reportes.cliente_360', [
                'clientes' => $clientes,
                'clienteSeleccionado' => null,
                'filtros' => $filtros,
                'resumen' => $this->emptyResumen(),
                'estadoPedidos' => collect(),
                'ventasMensuales' => collect(),
                'topProductos' => collect(),
                'ultimosPedidos' => collect(),
                'carteraDetalle' => collect(),
            ]);
        }

        $data = $this->buildData($clienteSeleccionado, $filtros);

        return view('admin.reportes.cliente_360', [
            'clientes' => $clientes,
            'clienteSeleccionado' => $clienteSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'estadoPedidos' => $data['estadoPedidos'],
            'ventasMensuales' => $data['ventasMensuales'],
            'topProductos' => $data['topProductos'],
            'ultimosPedidos' => $data['ultimosPedidos'],
            'carteraDetalle' => $data['carteraDetalle'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$clientes, $clienteSeleccionado, $filtros] = $this->resolverContexto($request);
        if (!$clienteSeleccionado) {
            return redirect()->route('admin.reportes.cliente.360')
                ->with('error', 'No se encontro cliente para exportar.');
        }

        $data = $this->buildData($clienteSeleccionado, $filtros);
        $filename = 'vision_360_cliente_' . ($clienteSeleccionado->codcli ?: 'sin_codcli') . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\Cliente360PedidosExport($data['pedidosExport']), $filename);
    }

    public function exportPdf(Request $request)
    {
        [$clientes, $clienteSeleccionado, $filtros] = $this->resolverContexto($request);
        if (!$clienteSeleccionado) {
            return redirect()->route('admin.reportes.cliente.360')
                ->with('error', 'No se encontro cliente para exportar.');
        }

        $data = $this->buildData($clienteSeleccionado, $filtros);
        $pdf = Pdf::loadView('admin.reportes.cliente_360_pdf', [
            'clienteSeleccionado' => $clienteSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'topProductos' => $data['topProductos'],
            'pedidosExport' => $data['pedidosExport'],
            'carteraDetalle' => $data['carteraDetalle'],
        ]);

        return $pdf->stream('vision_360_cliente_' . ($clienteSeleccionado->codcli ?: 'sin_codcli') . '.pdf');
    }

    private function resolverContexto(Request $request): array
    {
        $clientes = $this->getClientes();

        $clienteId = (string) $request->get('cliente_id', '');
        if ($clienteId === '' && $clientes->count() > 0) {
            $clienteId = (string) $clientes->first()->id_selector;
        }

        $filtros = [
            'cliente_id' => $clienteId,
            'fecha_desde' => $request->filled('fecha_desde') ? Carbon::parse($request->fecha_desde)->format('Y-m-d') : null,
            'fecha_hasta' => $request->filled('fecha_hasta') ? Carbon::parse($request->fecha_hasta)->format('Y-m-d') : null,
        ];

        if ($filtros['fecha_desde'] && $filtros['fecha_hasta'] && $filtros['fecha_desde'] > $filtros['fecha_hasta']) {
            $tmp = $filtros['fecha_desde'];
            $filtros['fecha_desde'] = $filtros['fecha_hasta'];
            $filtros['fecha_hasta'] = $tmp;
        }

        $clienteSeleccionado = $clientes->firstWhere('id_selector', $clienteId);

        return [$clientes, $clienteSeleccionado, $filtros];
    }

    private function getClientes()
    {
        $companyDb = DB::connection('company')->getDatabaseName();

        return DB::connection('company')
            ->table($companyDb . '.CLIENTE as c')
            ->selectRaw('CONCAT(COALESCE(c.CODCLI, ""), "|", COALESCE(c.RIF, "")) as id_selector')
            ->selectRaw('c.CODCLI as codcli')
            ->selectRaw('c.RIF as rif')
            ->selectRaw('c.NOMBRE as nombre')
            ->selectRaw('c.TELEFONO as telefono')
            ->selectRaw('c.EMAIL as email')
            ->whereRaw('TRIM(COALESCE(CONVERT(c.NOMBRE USING utf8mb4), "")) <> ""')
            ->orderBy('c.NOMBRE')
            ->limit(2000)
            ->get();
    }

    private function buildData($clienteSeleccionado, array $filtros): array
    {
        $fechaDesde = $filtros['fecha_desde'];
        $fechaHasta = $filtros['fecha_hasta'];

        $rifCliente = $this->normalizeRif((string) ($clienteSeleccionado->rif ?? ''));
        $codcli = trim((string) ($clienteSeleccionado->codcli ?? ''));

        $pedidosClienteFilter = function ($q) use ($rifCliente, $codcli) {
            if ($codcli !== '') {
                $q->where('p.codcli', $codcli);
            }

            if ($rifCliente !== '') {
                $method = $codcli !== '' ? 'orWhereRaw' : 'whereRaw';
                $q->{$method}('REPLACE(REPLACE(REPLACE(UPPER(COALESCE(p.rif, "")), "-", ""), " ", ""), ".", "") = ?', [$rifCliente]);
            }

            if ($codcli === '' && $rifCliente === '') {
                $q->whereRaw('1=0');
            }
        };

        $pedidosBase = DB::connection('company')->table('pedidos as p')->where(function ($q) use ($pedidosClienteFilter) {
            $pedidosClienteFilter($q);
        });
        $this->applyPedidosDateRange($pedidosBase, $fechaDesde, $fechaHasta);

        $pedidosValidos = DB::connection('company')
            ->table('pedidos as p')
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")');
        $this->applyPedidosDateRange($pedidosValidos, $fechaDesde, $fechaHasta);

        $resumenPedidos = (clone $pedidosBase)
            ->selectRaw('COUNT(*) as pedidos_total')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(p.estatus, "")) = "APROBADO" THEN 1 ELSE 0 END) as pedidos_aprobados')
            ->selectRaw('SUM(CASE WHEN UPPER(COALESCE(p.estatus, "")) = "PENDIENTE" THEN 1 ELSE 0 END) as pedidos_pendientes')
            ->first();

        $ultimaCompra = (clone $pedidosValidos)->max('p.fecha');

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
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
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
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
            ->selectRaw('p.id, p.fecha, p.referencia, p.descripcion, p.codcli, p.estatus')
            ->selectRaw('COALESCE(tp.total_unidades, 0) as total_unidades')
            ->selectRaw('COALESCE(tp.total_usd, 0) as total_usd');
        $this->applyPedidosDateRange($pedidosExport, $fechaDesde, $fechaHasta);
        $pedidosExport = $pedidosExport->orderByDesc('p.fecha')->orderByDesc('p.id')->get();

        $topProductos = DB::connection('company')
            ->table('pedidos as p')
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
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
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
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
            ->table('pagos_pedidos as pp')
            ->join('pagos as pg', 'pg.id', '=', 'pp.pago_id')
            ->join('pedidos as p', 'p.id', '=', 'pp.pedido_id')
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "APROBADO" THEN pp.monto ELSE 0 END), 0) as pagos_aprobados')
            ->selectRaw('COALESCE(SUM(CASE WHEN UPPER(COALESCE(pg.estatus, "")) = "EN REVISION" THEN pp.monto ELSE 0 END), 0) as pagos_revision');
        $this->applyPagosDateRange($pagosResumen, $fechaDesde, $fechaHasta, 'pg.fecha');
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
            ->where(function ($q) use ($pedidosClienteFilter) {
                $pedidosClienteFilter($q);
            })
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

        $diasSinComprar = null;
        if ($ultimaCompra) {
            $diasSinComprar = Carbon::parse($ultimaCompra)->diffInDays(Carbon::today());
        }

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
                'dias_sin_comprar' => $diasSinComprar,
                'ultima_compra' => $ultimaCompra,
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

    private function applyPagosDateRange($query, ?string $fechaDesde, ?string $fechaHasta, string $field): void
    {
        if ($fechaDesde) {
            $query->whereDate($field, '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate($field, '<=', $fechaHasta);
        }
    }

    private function normalizeRif(string $rif): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rif));
    }

    private function emptyResumen(): array
    {
        return [
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
            'dias_sin_comprar' => null,
            'ultima_compra' => null,
            'cobranza_vs_venta' => 0,
        ];
    }
}
