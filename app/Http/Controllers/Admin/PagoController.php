<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendedor;
use App\Exports\PagosExport;
use Maatwebsite\Excel\Facades\Excel;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $companyDb = DB::connection('company')->getDatabaseName();

        $query = DB::connection('company')->table(DB::raw($companyDb . '.pagos as pag'))
            ->join(DB::raw($companyDb . '.pagos_pedidos as pp'), 'pp.pago_id', '=', 'pag.id')
            ->join(DB::raw($companyDb . '.pedidos as p'), 'p.id', '=', 'pp.pedido_id')
            ->leftJoin(DB::raw($companyDb . '.vendedores as v'), 'v.id', '=', 'pag.seller_id')
            ->leftJoin(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'pag.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($companyDb . '.TPAGO as t'), 't.CPAGO', '=', 'pag.tpago_id')
            ->select(
                'pag.fecha as fecha_pago',
                'p.id as pedido_id',
                't.DPAGO as tipo_pago',
                'pag.referencia',
                DB::raw('pp.monto as monto'),
                'pag.rate as tasa',
                DB::raw('(pp.monto * pag.rate) as monto_bolivares'),
                'v.codigo as codigo_vendedor',
                'u.name as nombre_vendedor',
                'p.descripcion as cliente'
            );

        // Filtros
        if ($request->filled('vendedor')) {
            // Relación vendedores-usuarios por email
            $query->where('u.email', $request->vendedor);
        }

        if ($request->filled('tipo_pago')) {
            $query->where('pag.tpago_id', $request->tipo_pago);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('pag.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('pag.fecha', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('p.id', 'like', "%{$searchTerm}%")
                  ->orWhere('pag.referencia', 'like', "%{$searchTerm}%")
                  ->orWhere('p.descripcion', 'like', "%{$searchTerm}%");
            });
        }

        $sortField = $request->get('sort', 'fecha_pago');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $pagos = $query->paginate(20)->withQueryString();

        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('u.email', DB::raw("CONCAT(u.name) as nombre_completo"), 'v.codigo')
            ->orderBy('v.codigo')
            ->get();

        $tipos_pago = DB::connection('company')->table('TPAGO')->get();

        return view('pagos.index', compact('pagos', 'vendedores', 'tipos_pago'));
    }

    public function export(Request $request)
    {
        return Excel::download(new PagosExport($request->all()), 'pagos.xlsx');
    }

    public function aprobar(Request $request)
    {
        $query = \App\Models\Pago::with([
                'tipo_pago', 
                'banco', 
                'pago_pedidos.pedido', 
                'pago_destino', 
                'user' // Asumiendo que esta relación existe para el vendedor
            ]);

        // Filtro por Estado (Modificado para permitir ver Pagadas)
        if ($request->filled('estado')) {
            $query->where('estatus', $request->estado);
        } else {
            // Por defecto solo Pendientes y En Revisión
            $query->whereIn('estatus', ['PENDIENTE', 'EN REVISION']);
        }

        // Filtros
        if ($request->filled('vendedor')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', $request->vendedor);
            });
        }

        if ($request->filled('tipo_pago')) {
            $query->where('tpago_id', $request->tipo_pago);
        }
        
        if ($request->filled('estado')) {
            $query->where('estatus', $request->estado);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('referencia', 'like', "%{$searchTerm}%")
                  ->orWhereHas('pago_pedidos.pedido', function($sq) use ($searchTerm) {
                      $sq->where('id', 'like', "%{$searchTerm}%")
                         ->orWhere('descripcion', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $sortField = $request->get('sort', 'fecha');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $pagos = $query->paginate(20)->withQueryString();

        // Estadísticas
        $totalPendiente = \App\Models\Pago::where('estatus', 'PENDIENTE')
            ->join('pagos_pedidos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
            ->sum('pagos_pedidos.monto');

        $totalEnRevision = \App\Models\Pago::where('estatus', 'EN REVISION')
            ->join('pagos_pedidos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
            ->sum('pagos_pedidos.monto');
            
        $totalHoy = \App\Models\Pago::whereIn('estatus', ['PENDIENTE', 'EN REVISION'])
            ->whereDate('fecha', now()->toDateString())
            ->join('pagos_pedidos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
            ->sum('pagos_pedidos.monto');

        // Todos los vendedores registrados
        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('u.email', DB::raw('u.name as nombre_completo'), 'v.codigo')
            ->orderBy('v.codigo')
            ->get();

        $tipos_pago = DB::connection('company')->table('TPAGO')->get();

        return view('admin.pagos.aprobar', compact('pagos', 'vendedores', 'tipos_pago', 'totalPendiente', 'totalEnRevision', 'totalHoy'));
    }

    public function trazabilidad($pedido_id)
    {
        try {
            $companyDb = DB::connection('company')->getDatabaseName();
            
            // Obtener todos los pagos asociados al pedido
            $pagos = DB::connection('company')->table(DB::raw($companyDb . '.pagos as pag'))
                ->join(DB::raw($companyDb . '.pagos_pedidos as pp'), 'pp.pago_id', '=', 'pag.id')
                ->leftJoin(DB::raw(config('database.connections.mysql.database') . '.banks as bd'), 'bd.codigo', '=', 'pag.pago_destino_id')
                ->leftJoin(DB::raw($companyDb . '.TPAGO as t'), 't.CPAGO', '=', 'pag.tpago_id')
                ->where('pp.pedido_id', $pedido_id)
                ->select(
                    'pag.fecha',
                    'pp.monto',
                    'pag.moneda_pago',
                    'pag.referencia',
                    'pag.estatus',
                    'pag.rate',
                    'bd.nombre as banco_destino',
                    't.DPAGO as tipo_pago'
                )
                ->orderBy('pag.fecha', 'desc')
                ->get();

            return response()->json([
                'type' => 'success',
                'pagos' => $pagos,
                'message' => 'Trazabilidad de pagos cargada exitosamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener trazabilidad de pagos: ' . $e->getMessage());
            
            return response()->json([
                'type' => 'error',
                'message' => 'No se pudo cargar la información de pagos: ' . $e->getMessage()
            ], 500);
        }
    }
}
