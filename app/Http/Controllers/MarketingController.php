<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\MarketingRequest;
use App\Models\{Marketing, MarketingDetalle, OrderInven, OrderClient, Company};
use App\Traits\MarketingTrait;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendEmailPromotions;

class MarketingController extends Controller
{
    use MarketingTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'order-inven';
        $this->module = 'marketing';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission())  {
            abort(403);
        }
     
        if (requestAjaxOrJson($request)) {
            $marketings = (new Marketing)->getData();
            return $this->getJsonOrDatatableResponse($request, $marketings);
        }     
        return view($this->module.'.index');
    }

    public function store(MarketingRequest $request)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $product = (new OrderInven)->where('CODIGO',$request->codigo)->first();

        if (! $product && $request->tipo!='personalizado') {
            return redirect()->route('marketing.create');
        }

        $marketing = (new Marketing)->create([
            'codigo' => $request->codigo,
            'descripcion' => $product->DESCR,
            'estatus' => 'pendiente',
            'user_id' => auth()->user()->id,
            'mensaje' => $request->mensaje,
            'tipo' => $request->tipo,
            'contacto' => $request->contacto,
        ]);

        if ($marketing && $request->file('image')) {
            $this->savePhoto($request, $marketing);
        }

        return redirect()->route('marketing.edit', $marketing->id);
    }

    private function savePhoto($request, $marketing)
    {
        $file_name = $this->uploadPhoto($request);
        $marketing->image = $file_name;
        $marketing->save();
    }

    
    private function uploadPhoto($request)
    {
        $name = Storage::disk('local')->put('public/products', $request->image);
        return substr($name, 16); 
    }

    public function create()
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $products = (new OrderInven)->getData();

        $route = 'marketing.index';
        return view($this->module.'.create', compact(['route', 'products']));
    }

    public function update(MarketingRequest $request, $id)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }
        
        $marketing = (new Marketing)->where('id', $id)->first();
        if ( $marketing) {
            $marketing->codigo = $request->codigo;
            $marketing->descripcion = $request->descripcion;
            $marketing->mensaje = $request->mensaje;
            $marketing->tipo = $request->tipo;
            $marketing->save();
            
            return redirect()->route('marketing.index')->with('info', 'El registro ha sido modificado satisfactoriamente');
        }
        return redirect()->route('marketing.index')->with('danger', 'El registro no existe...');
    }

    public function edit(Request $request, $id)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $marketing = (new Marketing)->getData($id);
        $products = (new OrderInven)->getData();
        $clients = (new OrderClient)->getEmailData();
        
        $route = 'marketing.index';
        return view($this->module.'.edit', compact(['marketing', 'route', 'products', 'clients']));
    }

    public function show(Request $request, $id)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $marketing = (new Marketing)->getData($id);
        //$products = (new OrderInven)->getData();
        
        $route = 'marketing.index';
        return view($this->module.'.show', compact(['marketing', 'route']));
    }

    public function destroy(Request $request, $id)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $marketing = (new Marketing)->find($id);
        $marketing->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function whatsappMarketing($order)
    {
        $prod = (new OrderInven)->where('CODIGO', $order->codigo)->first();
        $producto = $prod->DESCR;
        
        foreach($order->marketing_detalle as $item) {
            $tlf = $item->email;
            $cliente = $item->nombre;
            
            $this->sendWsNotification('dacabe_nuevo_producto', urlencode($cliente), urlencode($producto), $tlf);
            $update = (new MarketingDetalle)->where('id', $item->id)->update(['estatus' => 'Procesado']);
        }

        $exists = (new MarketingDetalle)->where('marketing_id', $order->id)->where('estatus', 'Pendiente')->get();

        if (! sizeof($exists)) {
            $order->estatus = 'Procesado';
            $order->save();
        }

        return Response::json([
            'type' => 'success',
            'title' => 'Pedido enviado!',
            'text' => 'La campaña de whatsapp ha sido enviada satisfactoriamente.'
        ], 200);
    }

    private function sendWsNotification($template='dacabe_nuevo_producto', $cliente='Dacabe', $producto='', $phone='584129003985')
    {
        $url = 'https://santiscodes.com/send-whatsapp?to='.$phone.'&template='.$template.'&pedido_id='.$producto.'&cliente='.$cliente;
        $response = file_get_contents($url);
    }

    public function processMarketing(Request $request, $id)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $order = (new Marketing)->with('marketing_detalle_pendiente')->find($id);

        if (! $order) {
            abort(404);
        }

        if ($order->tipo=='whatsapp'){
            return $this->whatsappMarketing($order);
        }
        $print = null;
        $report_data = (new Marketing)->getReportConfig();
        $company = (new Company)->getMyCompany();
        
        $prod = (new OrderInven)->where('CODIGO', $order->codigo)->first();

        $data = $order->toArray();

        $data['title'] = ($order->tipo=='producto_nuevo' ? 'Te presentamos nuestro Nuevo Producto' : ($order->tipo=='producto_futuro' ? 'Productos por llegar': ($order->tipo=='personalizado' ? 'Tenemos un mensaje para ti' : 'Tenemos para ti un Producto en Promoción')));
        $data['company'] = $company;
        //$data['precio'] = $prod->PVPM1;
        $data['precio'] = $prod->BASE1;
        $data['foto'] = '';
        if ($prod->FOTO) {
            $data['foto'] = $prod->FOTO;
        }
        $data['image'] = '';
        if ($order->image) {
            $data['image'] = $order->image;
        }
        $data['url'] = url('inicio');

        foreach($order->marketing_detalle as $item) {
            $data['email'] = $item->email;
            $data['nombre'] = $item->nombre;
            
            dispatch(new \App\Jobs\SendEmailPromotions($data)); 

            $update = (new MarketingDetalle)->where('id', $item->id)->update(['estatus' => 'Procesado']);
        }

        $exists = (new MarketingDetalle)->where('marketing_id', $id)->where('estatus', 'Pendiente')->get();

        if (! sizeof($exists)) {
            $order->estatus = 'Procesado';
            //$order->date_at = now();
            $order->save();
        }

        return Response::json([
            'type' => 'success',
            'title' => 'Pedido enviado!',
            'text' => 'El Pedido ha sido enviado por email satisfactoriamente.'
        ], 200);
        
    }

    public function deleteMarketingDetail(Request $request)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $detalle = (new MarketingDetalle)->find($request->id);
        $marketing_id = (new Marketing)->find($detalle->marketing_id);
        $detalle->delete();
        
        $marketing = (new Marketing)->with('marketing_detalle')->find($marketing_id->id);

        return Response::json([
            'type' => 'success',
            'title' => 'Cliente actualizado!',
            'data' => $marketing,
            'text' => 'El cliente ha sido agregado a la campaña satisfactoriamente.'
        ], 200);
    }

    public function updateMarketingDetail(Request $request)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }

        $marketing = (new Marketing)->with('marketing_detalle')->find($request->id);
        
        if (! $marketing) {
            abort(404);
        }
        if ($request->data['nombre'] == 'TODOS') {
            if ($marketing->tipo=='whatsapp'){
                $clients = (new OrderClient)->where('TELEFONO', '!=', '')->where('TELEFONO', '!=', '-')->get();
                forEach($clients as $client) {
                    if ($client->TELEFONO) {
                        $detalle = $marketing->marketing_detalle->where('email', $client->TELEFONO)->first();
                        if (! $detalle) {
                            $detalle = new MarketingDetalle();
                            $detalle->marketing_id = $marketing->id;
                            $detalle->email = $client->TELEFONO;
                        }
                        $detalle->nombre = $client->NOMBRE;
                        $detalle->estatus = 'Pendiente';

                        $detalle->save();                     
                    }
                }
            } else {
                $clients = (new OrderClient)->where('EMAIL', '!=', '')->get();
                forEach($clients as $client) {
                    if ($client->EMAIL) {
                        $detalle = $marketing->marketing_detalle->where('email', $client->EMAIL)->first();
                        if (! $detalle) {
                            $detalle = new MarketingDetalle();
                            $detalle->marketing_id = $marketing->id;
                            $detalle->email = $client->EMAIL;
                        }
                        $detalle->nombre = $client->NOMBRE;
                        $detalle->estatus = 'Pendiente';

                        $detalle->save();                     
                    }
                }

            }
        } else {
            $detalle = $marketing->marketing_detalle->where('email', $request->data['email'])->first();
            if (! $detalle) {
                $detalle = new MarketingDetalle();
                $detalle->marketing_id = $marketing->id;
                $detalle->email = $request->data['email'];
            }
            $detalle->nombre = $request->data['nombre'];
            $detalle->estatus = 'Pendiente';

            $detalle->save();            
        }

        $marketing->load('marketing_detalle');
        
        return Response::json([
            'type' => 'success',
            'title' => 'Cliente actualizado!',
            'data' => $marketing,
            'text' => 'El cliente ha sido agregado a la campaña satisfactoriamente.'
        ], 200);
        
    }

}
