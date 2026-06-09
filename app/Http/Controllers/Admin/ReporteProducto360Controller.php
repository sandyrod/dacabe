<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteProducto360Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        [$productos, $productoSeleccionado, $filtros] = $this->resolverContexto($request);

        if (!$productoSeleccionado) {
            return view('admin.reportes.producto_360', [
                'productos' => $productos,
                'productoSeleccionado' => null,
                'filtros' => $filtros,
                'resumen' => $this->emptyResumen(),
                'estadoPedidos' => collect(),
                'ventasMensuales' => collect(),
                'topClientes' => collect(),
                'ultimosPedidos' => collect(),
            ]);
        }

        $data = $this->buildData($productoSeleccionado, $filtros);

        return view('admin.reportes.producto_360', [
            'productos' => $productos,
            'productoSeleccionado' => $productoSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'estadoPedidos' => $data['estadoPedidos'],
            'ventasMensuales' => $data['ventasMensuales'],
            'topClientes' => $data['topClientes'],
            'ultimosPedidos' => $data['ultimosPedidos'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$productos, $productoSeleccionado, $filtros] = $this->resolverContexto($request);

        if (!$productoSeleccionado) {
            return redirect()->route('admin.reportes.producto.360')
                ->with('error', 'No se encontro producto para exportar.');
        }

        $data = $this->buildData($productoSeleccionado, $filtros);
        $filename = 'vision_360_producto_' . ($productoSeleccionado->codigo ?: 'sin_codigo') . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\Producto360MovimientosExport($data['movimientosExport']), $filename);
    }

    public function exportPdf(Request $request)
    {
        [$productos, $productoSeleccionado, $filtros] = $this->resolverContexto($request);

        if (!$productoSeleccionado) {
            return redirect()->route('admin.reportes.producto.360')
                ->with('error', 'No se encontro producto para exportar.');
        }

        $data = $this->buildData($productoSeleccionado, $filtros);
        $pdf = Pdf::loadView('admin.reportes.producto_360_pdf', [
            'productoSeleccionado' => $productoSeleccionado,
            'filtros' => $filtros,
            'resumen' => $data['resumen'],
            'topClientes' => $data['topClientes'],
            'movimientosExport' => $data['movimientosExport'],
        ]);

        return $pdf->stream('vision_360_producto_' . ($productoSeleccionado->codigo ?: 'sin_codigo') . '.pdf');
    }

    private function resolverContexto(Request $request): array
    {
        $productos = $this->getProductos();

        $codigo = (string) $request->get('producto_codigo', '');
        if ($codigo === '' && $productos->count() > 0) {
            $codigo = (string) $productos->first()->codigo;
        }

        $filtros = [
            'producto_codigo' => $codigo,
            'fecha_desde' => $request->filled('fecha_desde') ? Carbon::parse($request->fecha_desde)->format('Y-m-d') : null,
            'fecha_hasta' => $request->filled('fecha_hasta') ? Carbon::parse($request->fecha_hasta)->format('Y-m-d') : null,
        ];

        if ($filtros['fecha_desde'] && $filtros['fecha_hasta'] && $filtros['fecha_desde'] > $filtros['fecha_hasta']) {
            $tmp = $filtros['fecha_desde'];
            $filtros['fecha_desde'] = $filtros['fecha_hasta'];
            $filtros['fecha_hasta'] = $tmp;
        }

        $productoSeleccionado = $productos->firstWhere('codigo', $codigo);

        return [$productos, $productoSeleccionado, $filtros];
    }

    private function getProductos()
    {
        return DB::connection('company')
            ->table('pedido_detalle as pd')
            ->selectRaw('pd.codigo_inven as codigo')
            ->selectRaw('MAX(pd.inven_descr) as descripcion')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades_historicas')
            ->whereNotNull('pd.codigo_inven')
            ->whereRaw('TRIM(COALESCE(pd.codigo_inven, "")) <> ""')
            ->groupBy('pd.codigo_inven')
            ->orderBy('pd.codigo_inven')
            ->limit(3000)
            ->get();
    }

    private function buildData($productoSeleccionado, array $filtros): array
    {
        $codigo = trim((string) ($productoSeleccionado->codigo ?? ''));
        $fechaDesde = $filtros['fecha_desde'];
        $fechaHasta = $filtros['fecha_hasta'];

        $base = DB::connection('company')
            ->table('pedido_detalle as pd')
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id')
            ->whereRaw('TRIM(COALESCE(pd.codigo_inven, "")) = ?', [$codigo]);

        $this->applyPedidosDateRange($base, $fechaDesde, $fechaHasta);

        $baseValidos = DB::connection('company')
            ->table('pedido_detalle as pd')
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id')
            ->whereRaw('TRIM(COALESCE(pd.codigo_inven, "")) = ?', [$codigo])
            ->whereRaw('UPPER(COALESCE(p.estatus, "")) NOT IN ("CARGANDO", "ANULADO", "CANCELADO")');

        $this->applyPedidosDateRange($baseValidos, $fechaDesde, $fechaHasta);

        $resumenGeneral = (clone $base)
            ->selectRaw('COUNT(DISTINCT p.id) as pedidos_total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN UPPER(COALESCE(p.estatus, "")) = "APROBADO" THEN p.id END) as pedidos_aprobados')
            ->selectRaw('COUNT(DISTINCT CASE WHEN UPPER(COALESCE(p.estatus, "")) = "PENDIENTE" THEN p.id END) as pedidos_pendientes')
            ->selectRaw('COUNT(DISTINCT COALESCE(NULLIF(TRIM(p.codcli), ""), CONCAT("RIF:", TRIM(COALESCE(p.rif, ""))))) as clientes_activos')
            ->first();

        $ventas = (clone $baseValidos)
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
            ->first();

        $estadoPedidos = (clone $base)
            ->selectRaw('COALESCE(NULLIF(TRIM(p.estatus), ""), "SIN ESTATUS") as estatus')
            ->selectRaw('COUNT(DISTINCT p.id) as cantidad')
            ->groupBy('estatus')
            ->orderByDesc('cantidad')
            ->get();

        $topClientes = (clone $baseValidos)
            ->selectRaw('COALESCE(NULLIF(TRIM(p.codcli), ""), "SIN CODCLI") as codcli')
            ->selectRaw('MAX(COALESCE(NULLIF(TRIM(p.descripcion), ""), "Cliente sin descripcion")) as cliente')
            ->selectRaw('COUNT(DISTINCT p.id) as pedidos')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
            ->groupBy('codcli')
            ->orderByDesc('ventas_usd')
            ->limit(10)
            ->get();

        $ultimosPedidos = (clone $base)
            ->selectRaw('p.id, p.fecha, p.estatus, p.codcli, p.descripcion as cliente, p.referencia')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as total_usd')
            ->groupBy('p.id', 'p.fecha', 'p.estatus', 'p.codcli', 'p.descripcion', 'p.referencia')
            ->orderByDesc('p.fecha')
            ->orderByDesc('p.id')
            ->limit(20)
            ->get();

        $movimientosExport = (clone $base)
            ->selectRaw('p.id, p.fecha, p.referencia, p.estatus, p.codcli, p.descripcion as cliente')
            ->selectRaw('pd.codigo_inven as codigo_producto')
            ->selectRaw('pd.inven_descr as descripcion_producto')
            ->selectRaw('COALESCE(pd.cantidad, 0) as unidades')
            ->selectRaw('COALESCE(pd.precio_dolar, 0) as precio_usd')
            ->selectRaw('COALESCE(pd.cantidad * pd.precio_dolar, 0) as total_usd')
            ->orderByDesc('p.fecha')
            ->orderByDesc('p.id')
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

        $mensualRaw = (clone $baseValidos)
            ->whereDate(DB::raw('COALESCE(p.fecha, p.created_at)'), '>=', $inicioMes->toDateString())
            ->selectRaw('DATE_FORMAT(COALESCE(p.fecha, p.created_at), "%Y-%m") as periodo')
            ->selectRaw('COALESCE(SUM(pd.cantidad), 0) as unidades')
            ->selectRaw('COALESCE(SUM(pd.cantidad * pd.precio_dolar), 0) as ventas_usd')
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
                'unidades' => (float) optional($mensualRaw->get($key))->unidades,
                'ventas_usd' => (float) optional($mensualRaw->get($key))->ventas_usd,
            ]);
        }

        $inventario = DB::connection('company')
            ->table('INVEN as i')
            ->leftJoin('ARTDEPOS as ad', 'ad.CODIGO', '=', 'i.CODIGO')
            ->whereRaw('TRIM(COALESCE(i.CODIGO, "")) = ?', [$codigo])
            ->selectRaw('MAX(i.CODIGO) as codigo')
            ->selectRaw('MAX(i.DESCR) as descripcion')
            ->selectRaw('MAX(COALESCE(i.SMIN, 0)) as stock_minimo')
            ->selectRaw('MAX(COALESCE(i.SMAX, 0)) as stock_maximo')
            ->selectRaw('MAX(COALESCE(i.BASE1, 0)) as precio_lista_usd')
            ->selectRaw('COALESCE(SUM(COALESCE(ad.EUNIDAD, 0)), 0) as stock_actual')
            ->first();

        $ventasUsd = (float) ($ventas->ventas_usd ?? 0);
        $unidades = (float) ($ventas->unidades ?? 0);
        $stockActual = (float) ($inventario->stock_actual ?? 0);
        $stockMinimo = (float) ($inventario->stock_minimo ?? 0);

        return [
            'resumen' => [
                'pedidos_total' => (int) ($resumenGeneral->pedidos_total ?? 0),
                'pedidos_aprobados' => (int) ($resumenGeneral->pedidos_aprobados ?? 0),
                'pedidos_pendientes' => (int) ($resumenGeneral->pedidos_pendientes ?? 0),
                'clientes_activos' => (int) ($resumenGeneral->clientes_activos ?? 0),
                'ventas_usd' => $ventasUsd,
                'unidades' => $unidades,
                'ticket_promedio' => ((int) ($resumenGeneral->pedidos_total ?? 0)) > 0 ? ($ventasUsd / (int) $resumenGeneral->pedidos_total) : 0,
                'precio_promedio' => $unidades > 0 ? ($ventasUsd / $unidades) : 0,
                'stock_actual' => $stockActual,
                'stock_minimo' => $stockMinimo,
                'stock_maximo' => (float) ($inventario->stock_maximo ?? 0),
                'precio_lista_usd' => (float) ($inventario->precio_lista_usd ?? 0),
                'stock_status_pct' => $stockMinimo > 0 ? (($stockActual / $stockMinimo) * 100) : 0,
            ],
            'estadoPedidos' => $estadoPedidos,
            'ventasMensuales' => $ventasMensuales,
            'topClientes' => $topClientes,
            'ultimosPedidos' => $ultimosPedidos,
            'movimientosExport' => $movimientosExport,
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

    private function emptyResumen(): array
    {
        return [
            'pedidos_total' => 0,
            'pedidos_aprobados' => 0,
            'pedidos_pendientes' => 0,
            'clientes_activos' => 0,
            'ventas_usd' => 0,
            'unidades' => 0,
            'ticket_promedio' => 0,
            'precio_promedio' => 0,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'stock_maximo' => 0,
            'precio_lista_usd' => 0,
            'stock_status_pct' => 0,
        ];
    }
}
