<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ProductoFuturoRequest;
use App\Models\{ProductoFuturo, OrderGrupo, Promocion};
use App\Traits\ProductoFuturoTrait;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductosFuturosController extends Controller
{
    use ProductoFuturoTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'order-inven';
        $this->module = 'productos_futuros';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        if (requestAjaxOrJson($request)) {
            $productos_futuros = (new ProductoFuturo)->getData();
            return $this->getJsonOrDatatableResponse($request, $productos_futuros);
        }     
        return view($this->module.'.index');
    }

    public function shoppingcart(Request $request)
    {
        //if ( ! hasPermission('pedidos') ) {
        //    abort(403);
        //}
     
        $productos_futuros = null; //(new ProductoFuturo)->getData();
        $groups = (new OrderGrupo)->getData();
     
        return view($this->module.'.shoppingcart', compact(['productos_futuros', 'groups']));
    }

    public function store(ProductoFuturoRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $producto_futuro = (new ProductoFuturo)->createNew($request);

        if ($producto_futuro && $request->file('foto')) {
            $this->savePhoto($request, $producto_futuro);
        }
       
        return redirect()->route('productos-futuros.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if (! hasOrderPermission()) 
            abort(403);

        $route = 'productos-futuros.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(ProductoFuturoRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $producto_futuro = (new ProductoFuturo)->where('codigo', $code)->first();
        if ( $producto_futuro) {
            $producto_futuro->codigo = $request->codigo;
            $producto_futuro->descripcion = $request->descricion;
            $producto_futuro->pronto = 1;
            $producto_futuro->save();
            
            if ($producto_futuro && $request->file('foto')) {
                $this->savePhoto($request, $producto_futuro);
            }
            return redirect()->route('productos-futuros.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
        }
        return redirect()->route('productos-futuros.index')
                ->with('danger', 'El registro no existe...');
    }

    private function savePhoto($request, $producto_futuro)
    {
        $file_name = $this->uploadPhoto($request);
        //(new ProductoFuturo)->updateItem($producto_futuro->codigo, $file_name, $request);
        (new ProductoFuturo)->where('codigo', $request->codigo)->update(['foto' => $file_name]);
    }

    
    private function uploadPhoto($request)
    {
        $name = Storage::disk('local')->put('public/products', $request->foto);
        return substr($name, 16); 
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission())  {
            abort(403);
        }

        $producto_futuro = (new ProductoFuturo)->getData($code);
        $promocion = (new Promocion)->where('codigo', $code)->first();

        $route = 'productos-futuros.index';
        return view($this->module.'.edit', compact(['producto_futuro', 'route', 'promocion']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $producto_futuro = (new ProductoFuturo)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $productos_futuros = (new ProductoFuturo)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('productos_futuros', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $producto_futuro = new ProductoFuturo();
        $productos_futuros = $producto_futuro->getData();
        $print = 1;
        
        $report_data = $producto_futuro->getReportConfig();

        return view($this->module.'.partials.print', compact(['productos_futuros', 'print', 'report_data']));
    }


}
