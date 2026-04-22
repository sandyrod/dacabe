<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\User;

class ProductMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = PedidoDetalle::select(
            'pedido_detalle.*',
            'pedidos.fecha as fecha_pedido',
            'pedidos.descripcion as cliente',
            'pedidos.seller_code',
            'pedidos.estatus as pedido_estatus'
        )
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalle.pedido_id');

        // Filtros
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pedido_detalle.codigo_inven', 'like', "%{$searchTerm}%")
                    ->orWhere('pedido_detalle.inven_descr', 'like', "%{$searchTerm}%")
                    ->orWhere('pedidos.descripcion', 'like', "%{$searchTerm}%")
                    ->orWhere('pedidos.id', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('vendedor')) {
            $query->where('pedidos.seller_code', $request->vendedor);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('pedidos.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('pedidos.fecha', '<=', $request->fecha_fin);
        }

        $sortField = $request->get('sort', 'pedidos.fecha');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Estadísticas (basadas en el query filtrado pero sin paginar)
        $statsQuery = clone $query;
        $totalProductos = $statsQuery->sum('pedido_detalle.cantidad');
        $totalMonto = $statsQuery->sum(DB::raw('pedido_detalle.cantidad * pedido_detalle.precio_dolar'));
        $totalPedidos = $statsQuery->distinct('pedido_detalle.pedido_id')->count('pedido_detalle.pedido_id');

        $movimientos = $query->paginate(20)->withQueryString();

        // Vendedores para el filtro
        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('v.codigo', DB::raw("CONCAT(v.codigo, ' - ', u.name) as nombre_completo"))
            ->orderBy('v.codigo')
            ->get();

        return view('admin.productos.movimiento', compact(
            'movimientos',
            'vendedores',
            'totalProductos',
            'totalMonto',
            'totalPedidos'
        ));
    }
}
