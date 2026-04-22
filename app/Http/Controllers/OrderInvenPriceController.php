<?php

namespace App\Http\Controllers;

use App\Models\OrderInven;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderInvenPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = OrderInven::query();

        // Filtering
        if ($request->filled('codigo')) {
            $query->where('CODIGO', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('descr')) {
            $query->where('DESCR', 'like', '%' . $request->descr . '%');
        }

        if ($request->filled('cgrupo') && $request->cgrupo != 'TODOS') {
            $query->where('CGRUPO', $request->cgrupo);
        }

        // Sorting
        $sort = $request->get('sort', 'DESCR');
        $direction = $request->get('direction', 'asc');
        
        // Validate sortable columns to prevent injection or errors
        $allowedSorts = ['CODIGO', 'DESCR', 'BASE1', 'BASE2'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('DESCR', 'asc');
        }

        // Statistics for the dashboard
        $stats = [
            'total_products' => OrderInven::count(),
            'avg_base1' => OrderInven::avg('BASE1'),
            'avg_base2' => OrderInven::avg('BASE2'),
            'max_price' => OrderInven::max('BASE1'),
        ];

        $groups = \App\Models\OrderGrupo::orderBy('DGRUPO')->get();

        $products = $query->with('artdepos')->paginate(50);

        return view('order_inven.modify_prices', compact('products', 'stats', 'groups'));
    }

    public function batchUpdate(Request $request)
    {
        try {
            $updates = $request->updates; // Array of {codigo, field, value}

            if (!is_array($updates) || empty($updates)) {
                return response()->json(['success' => false, 'message' => 'No hay cambios para procesar'], 400);
            }

            DB::beginTransaction();

            foreach ($updates as $update) {
                $code = $update['codigo'];
                $field = $update['field'];
                $value = $update['value'];

                if (in_array($field, ['BASE1', 'BASE2'])) {
                    OrderInven::where('CODIGO', $code)->update([$field => $value]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => count($updates) . ' precios actualizados correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePrice(Request $request)
    {
        // Keeping this for individual legacy/fallback but batch is preferred now
        try {
            $code = $request->codigo;
            $field = $request->field; 
            $value = $request->value;

            if (!in_array($field, ['BASE1', 'BASE2'])) {
                return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
            }

            OrderInven::where('CODIGO', $code)->update([$field => $value]);

            return response()->json(['success' => true, 'message' => 'Precio actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
