<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\ArtDepos;
use App\Models\Inven;
use Carbon\Carbon;

class ProductAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::connection('company')->table('pedido_detalle as pd')
            ->select([
                'pd.codigo_inven',
                'pd.inven_descr',
                'pd.cantidad',
                'pd.precio_dolar',
                'pd.precio',
                'p.fecha as fecha_pedido',
                'p.descripcion as cliente',
                'p.seller_code',
                'p.estatus as pedido_estatus',
                'p.id as pedido_id',
                DB::raw("'PEDIDO' as tipo_movimiento"),
                DB::raw("CONCAT('Pedido #', p.id) as referencia"),
                DB::raw('pd.cantidad * pd.precio_dolar as total_dolar')
            ])
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id');

        // Filtros principales
        if ($request->filled('producto')) {
            $searchTerm = $request->producto;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pd.codigo_inven', 'like', "%{$searchTerm}%")
                  ->orWhere('pd.inven_descr', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('cliente')) {
            $query->where('p.descripcion', 'like', "%{$request->cliente}%");
        }

        if ($request->filled('vendedor')) {
            $query->where('p.seller_code', $request->vendedor);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('p.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('p.fecha', '<=', $request->fecha_fin);
        }

        if ($request->filled('estatus')) {
            $query->where('p.estatus', $request->estatus);
        }

        // Clonar para estadísticas antes de aplicar paginación
        $statsQuery = clone $query;

        // Obtener movimientos de artdepos (sincronización externa)
        $artdeposQuery = DB::connection('company')->table('ARTDEPOS as ad')
            ->select([
                'ad.CODIGO as codigo_inven',
                'i.DESCR as inven_descr',
                DB::raw('ad.EUNIDAD as cantidad'),
                DB::raw('0 as precio_dolar'),
                DB::raw('0 as precio'),
                DB::raw('CURRENT_DATE as fecha_pedido'),
                DB::raw("'Sistema Dacabe Madre' as cliente"),
                DB::raw("'SYSTEM' as seller_code"),
                DB::raw("'ACTIVO' as pedido_estatus"),
                DB::raw('ad.CODDEPOS as pedido_id'),
                DB::raw("'SINCRONIZACION' as tipo_movimiento"),
                DB::raw("CONCAT('Depósito: ', COALESCE(d.DDEPOS, ad.CDEPOS)) as referencia"),
                DB::raw('0 as total_dolar')
            ])
            ->leftJoin('INVEN as i', 'i.CODIGO', '=', 'ad.CODIGO')
            ->leftJoin('DEPOSITO as d', 'd.CDEPOS', '=', 'ad.CDEPOS');

        // Aplicar mismos filtros para artdepos cuando sea aplicable
        if ($request->filled('producto')) {
            $searchTerm = $request->producto;
            $artdeposQuery->where(function ($q) use ($searchTerm) {
                $q->where('ad.CODIGO', 'like', "%{$searchTerm}%")
                  ->orWhere('i.DESCR', 'like', "%{$searchTerm}%");
            });
        }

        // Combinar resultados
        $allMovements = collect();

        // Movimientos de pedidos
        $pedidoMovements = $query->orderBy('p.fecha', 'desc')->get();
        $allMovements = $allMovements->merge($pedidoMovements);

        // Movimientos de sincronización (solo si se solicita)
        if ($request->filled('incluir_sincronizacion') && $request->incluir_sincronizacion == '1') {
            $sincronizationMovements = $artdeposQuery->get();
            $allMovements = $allMovements->merge($sincronizationMovements);
        }

        // Ordenar combinado
        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'desc');
            $allMovements = $allMovements->sortBy($request->sort, SORT_REGULAR, $direction === 'desc');
        } else {
            $allMovements = $allMovements->sortByDesc('fecha_pedido');
        }

        // Paginación manual para colección
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $movimientos = new \Illuminate\Pagination\LengthAwarePaginator(
            $allMovements->slice($offset, $perPage),
            $allMovements->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Estadísticas basadas en todos los movimientos
        $stats = [
            'total_productos_distintos' => $allMovements->pluck('codigo_inven')->unique()->count(),
            'total_unidades_movidas' => $allMovements->sum('cantidad'),
            'total_valor_dolar' => $allMovements->sum('total_dolar'),
            'total_pedidos' => $allMovements->where('tipo_movimiento', 'PEDIDO')->pluck('pedido_id')->unique()->count(),
            'total_sincronizaciones' => $allMovements->where('tipo_movimiento', 'SINCRONIZACION')->count(),
            'producto_mas_movido' => $allMovements->groupBy('codigo_inven')
                ->map->sum('cantidad')
                ->sortDesc()
                ->take(1)
                ->map(function ($cantidad, $codigo) use ($allMovements) {
                    $nombre = $allMovements->where('codigo_inven', $codigo)->first()->inven_descr ?? '';
                    return (object) [
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'cantidad' => $cantidad
                    ];
                })
                ->first()
        ];

        // Vendedores para el filtro
        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('v.codigo', DB::raw("CONCAT(v.codigo, ' - ', u.name) as nombre_completo"))
            ->orderBy('v.codigo')
            ->get();

        // Productos populares para filtros rápidos
        $productosPopulares = DB::connection('company')->table('pedido_detalle')
            ->select('codigo_inven', 'inven_descr', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('codigo_inven', 'inven_descr')
            ->orderByDesc('total_cantidad')
            ->limit(20)
            ->get();

        return view('admin.productos.auditoria', compact(
            'movimientos',
            'vendedores',
            'productosPopulares',
            'stats'
        ));
    }

    public function detalleProducto(Request $request, $codigo)
    {
        $producto = DB::connection('company')->table('INVEN')->where('CODIGO', $codigo)->first();
        
        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado');
        }

        // Historial completo del producto
        $historial = DB::connection('company')->table('pedido_detalle as pd')
            ->select([
                'pd.*',
                'p.fecha as fecha_pedido',
                'p.descripcion as cliente',
                'p.seller_code',
                'p.estatus as pedido_estatus',
                'p.id as pedido_id',
                DB::raw("'PEDIDO' as tipo_movimiento"),
                DB::raw("CONCAT('Pedido #', p.id) as referencia")
            ])
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id')
            ->where('pd.codigo_inven', $codigo)
            ->orderByDesc('p.fecha');

        if ($request->filled('fecha_inicio')) {
            $historial->whereDate('p.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $historial->whereDate('p.fecha', '<=', $request->fecha_fin);
        }

        $movimientos = $historial->paginate(30);

        // Estadísticas del producto
        $stats = [
            'total_ventas' => $historial->clone()->sum('cantidad'),
            'total_ingresos' => $historial->clone()->sum(DB::raw('cantidad * precio_dolar')),
            'precio_promedio' => $historial->clone()->avg('precio_dolar'),
            'clientes_unicos' => $historial->clone()->distinct('p.descripcion')->count('p.descripcion'),
            'pedidos_afectados' => $historial->clone()->distinct('p.id')->count('p.id'),
            'primer_venta' => $historial->clone()->min('p.fecha'),
            'ultima_venta' => $historial->clone()->max('p.fecha')
        ];

        // Stock actual en artdepos
        $stockActual = DB::connection('company')
            ->table('ARTDEPOS as ad')
            ->select('ad.EUNIDAD', 'ad.ECAJA', 'ad.CDEPOS', 'ad.RESERVA', 'd.DDEPOS as NOMBRE')
            ->leftJoin('DEPOSITO as d', 'd.CDEPOS', '=', 'ad.CDEPOS')
            ->where('ad.CODIGO', $codigo)
            ->get();

        return view('admin.productos.detalle_auditoria', compact(
            'producto',
            'movimientos',
            'stats',
            'stockActual'
        ));
    }

    public function export(Request $request)
    {
        $query = DB::connection('company')->table('pedido_detalle as pd')
            ->select([
                'pd.codigo_inven',
                'pd.inven_descr',
                'pd.cantidad',
                'pd.precio_dolar',
                'p.fecha as fecha_pedido',
                'p.descripcion as cliente',
                'p.seller_code',
                'p.estatus as pedido_estatus',
                'p.id as pedido_id',
                DB::raw("'PEDIDO' as tipo_movimiento"),
                DB::raw("CONCAT('Pedido #', p.id) as referencia"),
                DB::raw('pd.cantidad * pd.precio_dolar as total_dolar')
            ])
            ->join('pedidos as p', 'p.id', '=', 'pd.pedido_id');

        // Aplicar mismos filtros que en index
        if ($request->filled('producto')) {
            $searchTerm = $request->producto;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pd.codigo_inven', 'like', "%{$searchTerm}%")
                  ->orWhere('pd.inven_descr', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('p.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('p.fecha', '<=', $request->fecha_fin);
        }

        $movimientos = $query->orderByDesc('p.fecha')->get();

        // Generar CSV
        $filename = 'auditoria_productos_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($movimientos) {
            $file = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($file, [
                'Fecha',
                'Tipo Movimiento',
                'Referencia',
                'Código Producto',
                'Nombre Producto',
                'Cantidad',
                'Precio Dólar',
                'Total Dólar',
                'Cliente',
                'Vendedor',
                'Estatus'
            ]);

            foreach ($movimientos as $mov) {
                fputcsv($file, [
                    $mov->fecha_pedido,
                    $mov->tipo_movimiento,
                    $mov->referencia,
                    $mov->codigo_inven,
                    $mov->inven_descr,
                    $mov->cantidad,
                    $mov->precio_dolar,
                    $mov->total_dolar,
                    $mov->cliente,
                    $mov->seller_code,
                    $mov->pedido_estatus
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtener detalle del pedido para la modal
     */
    public function getDetallePedidoAjax($pedidoId)
    {
        $pedido = DB::connection('company')->table('pedidos as p')
            ->select('p.id', 'p.fecha', 'p.descripcion as cliente', 'p.seller_code', 'p.estatus')
            ->where('p.id', $pedidoId)
            ->first();

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $detalles = DB::connection('company')->table('pedido_detalle as pd')
            ->select('pd.codigo_inven as codigo', 'pd.inven_descr as nombre', 
                    'pd.cantidad', 'pd.precio_dolar as precio',
                    DB::raw('pd.cantidad * pd.precio_dolar as total'))
            ->where('pd.pedido_id', $pedidoId)
            ->get();

        $totalPedido = $detalles->sum('total');

        // Determinar clase para el badge de estatus
        $estatusClass = 'secondary';
        if ($pedido->estatus == 'APROBADO' || $pedido->estatus == 'ENTREGADO') $estatusClass = 'success';
        if ($pedido->estatus == 'PENDIENTE') $estatusClass = 'warning';
        if ($pedido->estatus == 'RECHAZADO' || $pedido->estatus == 'ANULADO') $estatusClass = 'danger';

        return response()->json([
            'pedido' => [
                'id' => $pedido->id,
                'fecha' => \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y H:i'),
                'cliente' => $pedido->cliente,
                'vendedor' => $pedido->seller_code,
                'estatus' => $pedido->estatus,
                'estatus_class' => $estatusClass,
                'total' => number_format($totalPedido, 2, ',', '.')
            ],
            'productos' => $detalles->map(function($detalle) {
                return [
                    'codigo' => $detalle->codigo,
                    'nombre' => $detalle->nombre,
                    'cantidad' => number_format($detalle->cantidad, 0, ',', '.'),
                    'precio' => number_format($detalle->precio, 2, ',', '.'),
                    'total' => number_format($detalle->total, 2, ',', '.')
                ];
            })
        ]);
    }
}
