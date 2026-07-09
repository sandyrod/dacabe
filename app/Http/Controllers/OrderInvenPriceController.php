<?php

namespace App\Http\Controllers;

use App\Models\OrderInven;
use App\Models\InvenInformacion;
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
        $allowedSorts = ['CODIGO', 'DESCR', 'BASE1', 'BASE2', 'BASE3', 'BASE4'];
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

                if (in_array($field, ['BASE1', 'BASE2', 'BASE3', 'BASE4'])) {
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

            if (!in_array($field, ['BASE1', 'BASE2', 'BASE3', 'BASE4'])) {
                return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
            }

            OrderInven::where('CODIGO', $code)->update([$field => $value]);

            return response()->json(['success' => true, 'message' => 'Precio actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function indexComision(Request $request)
    {
        $query = OrderInven::query()
            ->select('INVEN.*')
            ->addSelect('inven_informacion.comision as COMISION')
            ->leftJoin('inven_informacion', function ($join) {
                $join->on(DB::raw('BINARY inven_informacion.codigo'), '=', DB::raw('BINARY INVEN.CODIGO'));
            });

        // Filtering
        if ($request->filled('codigo')) {
            $query->where('INVEN.CODIGO', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('descr')) {
            $query->where('INVEN.DESCR', 'like', '%' . $request->descr . '%');
        }

        if ($request->filled('cgrupo') && $request->cgrupo != 'TODOS') {
            $query->where('INVEN.CGRUPO', $request->cgrupo);
        }

        if ($request->filled('comision_filter') && $request->comision_filter != 'TODOS') {
            if ($request->comision_filter == 'CON') {
                $query->where('inven_informacion.comision', '>', 0);
            } elseif ($request->comision_filter == 'SIN') {
                $query->where(function ($q) {
                    $q->whereNull('inven_informacion.comision')
                        ->orWhere('inven_informacion.comision', '<=', 0);
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'DESCR');
        $direction = $request->get('direction', 'asc');

        // Validate sortable columns to prevent injection or errors
        $allowedSorts = [
            'CODIGO' => 'INVEN.CODIGO',
            'DESCR' => 'INVEN.DESCR',
            'COMISION' => 'inven_informacion.comision',
        ];
        if (array_key_exists($sort, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sort], $direction);
        } else {
            $query->orderBy('INVEN.DESCR', 'asc');
        }

        // Statistics for the dashboard
        $totalProducts = OrderInven::count();
        $conComision = InvenInformacion::where('comision', '>', 0)->count();
        $stats = [
            'total_products' => $totalProducts,
            'con_comision' => $conComision,
            'sin_comision' => $totalProducts - $conComision,
            'avg_comision' => InvenInformacion::where('comision', '>', 0)->avg('comision'),
        ];

        $groups = \App\Models\OrderGrupo::orderBy('DGRUPO')->get();

        $products = $query->paginate(50);

        return view('order_inven.modify_comisiones', compact('products', 'stats', 'groups'));
    }

    public function batchUpdateComision(Request $request)
    {
        try {
            $updates = $request->updates; // Array of {codigo, value}

            if (!is_array($updates) || empty($updates)) {
                return response()->json(['success' => false, 'message' => 'No hay cambios para procesar'], 400);
            }

            DB::beginTransaction();

            foreach ($updates as $update) {
                $code = $update['codigo'];
                $value = $update['value'];

                $informacion = InvenInformacion::where('codigo', $code)->first();
                if (!$informacion) {
                    $informacion = new InvenInformacion();
                    $informacion->codigo = $code;
                    $informacion->detalle = ' ';
                }
                $informacion->comision = $value;
                $informacion->save();
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => count($updates) . ' comisiones actualizadas correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
