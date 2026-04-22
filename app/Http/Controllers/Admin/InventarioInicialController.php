<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventarioInicial;
use App\Models\OrderInven;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InventarioInicialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the inventory initial loading page.
     */
    public function index()
    {
        $productos = OrderInven::orderBy('DESCR')
            ->get(['DESCR', 'CODIGO']);

        // Get recent inventory initial records
        $recientes = InventarioInicial::withProduct()
            ->with('user')
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.inventario.inventario_inicial', compact('productos', 'recientes'));
    }

    /**
     * Store new inventory initial records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'productos' => 'required|string',
            'observacion' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Decode JSON productos array
            $productos = json_decode($request->productos, true);
            
            if (!is_array($productos) || empty($productos)) {
                return response()->json(['success' => false, 'message' => 'No se encontraron productos válidos'], 400);
            }

            // Validate each product
            foreach ($productos as $index => $item) {
                if (!isset($item['codigo']) || !isset($item['cantidad'])) {
                    return response()->json(['success' => false, 'message' => "El producto {$index} no tiene código o cantidad"], 400);
                }
                
                // Check if product exists in INVEN table (try CODIGO first, then ALTERNO)
                $productoExists = OrderInven::where(function ($q) use ($item) {
                    $q->where('CODIGO', $item['codigo'])
                      ->orWhere('ALTERNO', $item['codigo']);
                })->first();
                
                if (!$productoExists) {
                    return response()->json(['success' => false, 'message' => "El producto con código '{$item['codigo']}' no existe en el inventario"], 400);
                }
                
                // Validate quantity is numeric and positive
                if (!is_numeric($item['cantidad']) || $item['cantidad'] < 0) {
                    return response()->json(['success' => false, 'message' => "La cantidad del producto {$index} debe ser un número mayor o igual a 0"], 400);
                }
            }

            foreach ($productos as $item) {
                // Only save if quantity is greater than 0
                if ($item['cantidad'] > 0) {
                    InventarioInicial::create([
                        'user_id' => Auth::id(),
                        'codigo' => $item['codigo'],
                        'cantidad' => $item['cantidad'],
                        'fecha' => $request->fecha,
                        'observacion' => $request->observacion,
                    ]);

                    // Nota: No actualizamos INVEN directamente.
                    // El inventario inicial se maneja por separado
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Inventario inicial cargado exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en inventario inicial: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar el inventario inicial: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get inventory initial records for AJAX requests.
     */
    public function getRecords(Request $request)
    {
        $query = InventarioInicial::withProduct()
            ->with('user');

        // Filter by date range
        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        // Filter by product
        if ($request->producto_id) {
            $query->where('codigo', $request->producto_id);
        }

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $records
        ]);
    }

    /**
     * Delete an inventory initial record.
     */
    public function destroy($id)
    {
        try {
            // Verificar autenticación
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $record = InventarioInicial::findOrFail($id);
            
            // Simplemente eliminar el registro de inventario inicial
            // No actualizamos INVEN directamente
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error eliminando inventario inicial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total_registros' => InventarioInicial::count(),
            'cantidad_total' => InventarioInicial::sum('cantidad'),
            'ultimo_registro' => InventarioInicial::latest()->first() ? 
                InventarioInicial::latest()->first()->created_at->format('d/m/Y H:i') : 'Sin registros'
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Search products for Select2.
     * Usa CODIGO y ALTERNO como identificadores válidos
     */
    public function searchProductos(Request $request)
    {
        $search = $request->get('q', '');
        
        try {
            $items = OrderInven::where(function ($q) use ($search) {
                $q->where('DESCR', 'LIKE', "%{$search}%")
                  ->orWhere('CODIGO', 'LIKE', "%{$search}%")
                  ->orWhere('ALTERNO', 'LIKE', "%{$search}%");
            })
            ->select(['CODIGO', 'DESCR', 'ALTERNO'])
            ->limit(50)
            ->get()
            ->map(function ($item) {
                // Usar CODIGO como ID principal, ALTERNO como fallback
                $codigo_valido = (string)$item->CODIGO;
                if (empty($codigo_valido) || $codigo_valido == '0') {
                    $codigo_valido = (string)$item->ALTERNO;
                }
                
                return [
                    'id' => $codigo_valido,
                    'CODIGO' => $codigo_valido,
                    'DESCR' => $item->DESCR,
                    'ALTERNO' => $item->ALTERNO
                ];
            })
            ->toArray();
            
            return response()->json([
                'results' => $items,
                'data' => $items,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => count($items),
                'total' => count($items)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
