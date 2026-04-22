<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\OrderInvenRequest;
use App\Models\{OrderInven, OrderGrupo, Promocion, VendedorDeposito, Vendedor, Pedido, InvenFoto, InvenInformacion, Rate, DescuentoGlobal};
use App\Exports\ProductListExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\OrderInvenTrait;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OrderInvenController extends Controller
{
    use OrderInvenTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'order-inven';
        $this->module = 'order_inven';
    }

    /**
     * Exportar lista de productos a Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportProducts(Request $request)
    {
        try {
            $cdepos = $request->cdepos;
            $cgrupo = $request->cgrupo;
            $search = $request->search;
            
            $filename = 'lista_productos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(
                new ProductListExport($cdepos, $cgrupo, $search),
                $filename
            );
            
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Error al exportar la lista de productos: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al exportar la lista de productos: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        if (requestAjaxOrJson($request)) {
            $order_invens = (new OrderInven)->getData();
            return $this->getJsonOrDatatableResponse($request, $order_invens);
        }
        $descuento = obtenerDescuentoGlobal() ?? 0;

        return view($this->module.'.index', compact(['descuento']));
    }

    public function getProducts (Request $request)
    {
        $vendedor = (new Vendedor)->where('email', auth()->user()->email)->first();
        if (! $vendedor) {
            return null;
        }
        $order = (new Pedido)->where('user_id', auth()->user()->id)->where('estatus', 'CARGANDO')->first();
        $cdepos = ($order && $order->cdepos) ? $order->cdepos : @$request->cdepos;
        $depos = (new VendedorDeposito)->with('deposito')->where('vendedor_id', $vendedor->id)->get();
        if (! $cdepos) {
            $cdepos = ! $depos ? '' : (sizeof($depos)>1 ? $depos[0]->CDEPOS : ($depos[0]->CDEPOS));
        }
        $products = (new OrderInven)->getGroupProducts($vendedor, $cdepos, $request->cgrupo, $request->search);
        $rate = (new Rate)->gatLastRate();
        //$discount = ($rate) ? $rate->discount : 0;
        $discount = obtenerDescuentoGlobal() ?? 0;
        $config = (new DescuentoGlobal)->first();
        $show_precio1 = $config && $config->show_precio1 == 'SI' ? 'SI' : 'NO';
        $descuento = $config->discount;

        $productos_nacionales = (new OrderGrupo)->where('DGRUPO', 'like', '%NACIONAL%')->first();
        $productos_laminas = (new OrderGrupo)->where('DGRUPO', 'LAMINAS')->first();

        return Response::json([
            'type' => 'success',
            'data' => $products,
            'depos' => $depos,
            'cdepos' => $cdepos,
            'order' => $order,
            'discount' => $discount,
            //'dacabe_percent' => env('DACABE_PERCENT', 35),
            'dacabe_percent' => $discount,
            'show_precio1' => $show_precio1,
            'descuento' => $descuento,
            'productos_nacionales' => $productos_nacionales,
            'productos_laminas' => $productos_laminas,
            'recargo' => $vendedor->recargo ?? 0
        ], 200);
    }

    public function shoppingcart(Request $request)
    {
        //if ( ! hasPermission('pedidos') ) {
        //    abort(403);
        //}
     
        $order_invens = null; //(new OrderInven)->getData();
        $groups = (new OrderGrupo)->getData();
        $descuento = obtenerDescuentoGlobal() ?? 0;
     
        return view($this->module.'.shoppingcart', compact(['order_invens', 'groups', 'descuento']));
    }

    public function store(OrderInvenRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $order_inven = (new OrderInven)->createNew($request);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if (! hasOrderPermission()) 
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function storePhoto(Request $request)
    {
        if ($request->mode && $request->mode=='get') {            
            return Response::json([
                'type' => 'success',
                'title' => 'Foto eliminada!',
                'text' => 'El registro ha sido eliminado satisfactoriamente.',
                'data' => (new InvenFoto)->where('codigo', $request->product_id)->get()
            ], 200);
        }

        if ($request->mode && $request->mode=='delete') {
            $model = (new InvenFoto)->find($request->id);            
            $codigo = $model->codigo;
            $model->delete();
            $data = (new InvenFoto)->where('codigo', $codigo)->get();

            return Response::json([
                'type' => 'success',
                'title' => 'Foto eliminada!',
                'text' => 'El registro ha sido eliminado satisfactoriamente.',
                'data' => $data
            ], 200);
        }

        $name = Storage::disk('local')->put('public/products', $request->photo);
        (new InvenFoto)->create(['codigo'=>$request->codigo_inven, 'foto'=>substr($name, 16)]);

        return Response::json([
            'type' => 'success',
            'title' => 'Foto agregada!',
            'text' => 'La foto ha sido agregada satisfactoriamente.',
            'data' => (new InvenFoto)->where('codigo', $request->codigo_inven)->get()
        ], 200);
    }

    public function update(OrderInvenRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        //$order_inven = (new OrderInven)->updateItem($code, $request);

        $order_inven = (new OrderInven)->getProduct($code);
        if ($request->base1 || $request->base2) {
            (new OrderInven)->updatePrices($code, $request->base1, $request->base2);
        }
        if ($request->detalle || $request->observaciones || $request->stock_minimo || $request->comision) {
            (new InvenInformacion)->saveDetails($code, $request);
        }
        if (! $request->promocion && ! $request->nuevo) {
            (new Promocion)->where('codigo', $code)->delete();
        } else {
            $promocion = (new Promocion)->where('codigo', $code)->first();
            if (! $promocion) {
                $promocion = (new Promocion);
                $promocion->codigo = $code;
            }
            $promocion->promocion = $request->promocion;
            $promocion->nuevo = $request->nuevo;
            $promocion->save();
        }
        if ($order_inven) {
            (new OrderInven)->updateItemSmin($order_inven->CODIGO, $request);            
        }



        if ($order_inven && $request->file('photo')) {
            $this->savePhoto($request, $order_inven);
        }
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    private function savePhoto($request, $order_inven)
    {
        $file_name = $this->uploadPhoto($request);
        (new OrderInven)->updateItem($order_inven->CODIGO, $file_name);
    }

    
    private function uploadPhoto($request)
    {
        $name = Storage::disk('local')->put('public/products', $request->photo);
        return substr($name, 16); 
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission())  {
            abort(403);
        }

        $order_inven = (new OrderInven)->getData($code);
        $promocion = (new Promocion)->where('codigo', $code)->first();

        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['order_inven', 'route', 'promocion']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $order_inven = (new OrderInven)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $order_invens = (new OrderInven)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('order_invens', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $order_inven = new OrderInven();
        $order_invens = $order_inven->getData();
        $print = 1;
        
        $report_data = $order_inven->getReportConfig();

        return view($this->module.'.partials.print', compact(['order_invens', 'print', 'report_data']));
    }

    public function guardarDescuento (Request $request)
    {
        $discount = (new DescuentoGlobal)->first();
        if (! $discount) {
            $discount = (new DescuentoGlobal);
        }
        $discount->porcentaje = $request->discount ?? 0;
        $discount->save();

        return Response::json([
            'type' => 'success',
            'data' => $discount
        ], 200);
    }

    public function verDescuentoGlobal()
    {
        $descuento_global = (new DescuentoGlobal)->first();

        return view($this->module.'.descuento_global', compact(['descuento_global']));
    }

    public function actualizarDescuentoGlobal(Request $request)
    {
        $descuento_global = (new DescuentoGlobal)->first();
        $descuento_global->ref1 = ($request->ref1 && $request->ref1 > 0) ? $request->ref1 : 0;
        $descuento_global->ref2 = ($request->ref2 && $request->ref2 > 0) ? $request->ref2 : 0;
        $descuento_global->show_precio1 = ($request->show_precio1 && $request->show_precio1 == 'SI') ? 'SI' : 'NO';
        $descuento_global->save();

        $info = 'El registro ha sido modificado satisfactoriamente';

        return view($this->module.'.descuento_global', compact(['descuento_global', 'info']));
    }



}
