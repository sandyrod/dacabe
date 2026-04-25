<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
//use PDF;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Requests\OrderInvenRequest;
use App\Models\{Pedido, PedidoDetalle, OrderInven, OrderGrupo, OrderClient, Vendedor, Pago, Company, ArtDepos, PagoPedido, Tasa, ClienteVendedor};
use App\User;
use App\Traits\PedidosTrait;
use App\Jobs\SendEmailInvoice;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    use PedidosTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'pedidos';
        $this->module = 'pedidos';
    }

    public function index(Request $request)
    {
        if (!hasOrderPermission() && !hasOrderClientPermission())
            abort(403);

        if (requestAjaxOrJson($request)) {
            $pedidos = (new Pedido)->getData();
            return $this->getJsonOrDatatableResponse($request, $pedidos);
        }
        return view($this->module . '.index');
    }

    public function getProducts(Request $request)
    {
        return Response::json([
            'type' => 'success',
            'data' => (new OrderInven)->getGroupProductsByDepos($request->cgrupo)
        ], 200);
    }

    public function shoppingcart(Request $request)
    {
        if (!hasPermission('pedidos')) {
            abort(403);
        }

        $order_invens = null; //(new OrderInven)->getData();
        $groups = (new OrderGrupo)->getData();

        return view($this->module . '.shoppingcart', compact(['order_invens', 'groups']));
    }

    public function viewCart(Request $request)
    {
        /*
        if ( ! hasPermission('pedidos') ) {
            abort(403);
        }
        */

        $pedido = (new Pedido)->searchPendingOrder();
        $detalle = null;
        if ($pedido) {
            $detalle = (new Pedido)->getActiveOrderDetail();
        }
        $clients = (new OrderClient)->getData(null, Auth::user()->email);
        $descuento = obtenerDescuentoGlobal() ?? 0;

        return view('order_inven.view_cart', compact(['pedido', 'detalle', 'clients', 'descuento']));
    }

    public function viewOrder(Request $request, $order_id)
    {
        /*
        if ( ! hasPermission('pedidos') ) {
            abort(403);
        }
        */

        $pedido = (new Pedido)->getOrderById($order_id);
        $detall = null;
        if ($pedido) {
            $detalle = (new PedidoDetalle)->searchOrderDetail($pedido->id);
        }

        return view('order_inven.view_order', compact(['pedido', 'detalle']));
    }

    public function modifyQty(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $detalle = (new PedidoDetalle)->searchProductById($request->item_id);
        if ($detalle) {
            $qty = $request->operation == 'minus' ? $detalle->cantidad - 1 : ($request->operation == 'plus' ? $detalle->cantidad + 1 : $detalle->cantidad);
            if ($request->operation == 'set') {
                $qty = $request->qty;
            }
            if ($qty > 0) {
                $pedido = (new Pedido)->find($detalle->pedido_id);
                $user = (new User)->find($pedido->user_id);
                $seller = (new Vendedor)->where('email', $user->email)->first();
                if ($seller) {
                    $cdepos = $pedido->cdepos;
                    $artdepos = (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->first();
                    if ($artdepos) {
                        //$qty = $artdepos->reserva > 1 ? $artdepos->reserva - $detalle->cantidad : 0;
                        $dif = $artdepos->RESERVA - $detalle->cantidad;
                        //dd($request->cantidad);
                        (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $qty + $dif]);
                    }
                }

                $edit = (new PedidoDetalle)->updateItem($request->item_id, 'cantidad', $qty);
                return Response::json([
                    'title' => 'Genial!',
                    'text' => 'Cantidad Actualizada...',
                    'type' => 'success',
                    'data' => (new PedidoDetalle)->searchOrderDetail($detalle->pedido_id),
                    'qty' => $qty
                ], 200);
            } else {
                return Response::json([
                    'title' => 'Oopsss!',
                    'text' => 'La cantidad no se puede disminuir...',
                    'type' => 'error',
                    'qty' => $detalle->cantidad
                ], 200);
            }
        }

        return Response::json([
            'type' => 'error',
            'message' => 'Producto no encontrado...'
        ], 200);
    }

    public function deleteItem(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);
        $detalle = (new PedidoDetalle)->find($request->item_id);
        $pedido_id = @$detalle->pedido_id;
        if ((new PedidoDetalle)->deleteProductById($request->item_id)) {
            if (!(new PedidoDetalle)->where('pedido_id', $pedido_id)->exists()) {
                (new Pedido)->find($pedido_id)->delete();
            }
            return Response::json([
                'title' => 'Genial!',
                'text' => 'Item Eliminado...',
                'data' => (new PedidoDetalle)->searchOrderDetail($detalle->pedido_id),
                'type' => 'success'
            ], 200);
        }
        return Response::json([
            'type' => 'error'
        ], 200);
    }

    public function addToCart(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $pedido = (new Pedido)->searchPendingOrder();
        $descuento = obtenerDescuentoGlobal() ?? 0;
        if (!$pedido) {
            $pedido = (new Pedido)->createNew('', '', '', auth()->user()->name, '', '', 'CARGANDO', $request->cdepos, '', '', $descuento);
        }
        $pedido->descuento = $descuento;
        $pedido->save();
        if ($pedido->cdepos != $request->cdepos) {
            return Response::json([
                'type' => 'error',
                'message' => 'Tiene un pedido cargado para otro depósito, debe finalizarlo o anularlo! '
            ], 200);
        }

        $nacionales = (new OrderGrupo)->where('DGRUPO', 'PRODUCTOS NACIONALES')->first();
        $inven = (new OrderInven)->getProduct($request->codigo);
        if (!$inven) {
            return Response::json([
                'type' => 'error',
                'message' => 'Producto No Existe'
            ], 200);
        }
        $detalle = (new PedidoDetalle)->searchProduct($pedido->id, $inven->CODIGO);
        if (!$detalle) {
            $todayTasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
            $tasa = $todayTasa ? $todayTasa->valor : 0;
            /*
            $iva = 0;
            if ($nacionales && $inven->CGRUPO==$nacionales->CGRUPO){
                $iva = round($request->precio, 2)*0.16;
            }
            $detalle = (new PedidoDetalle)->createNew($pedido->id, $request->cantidad, round($request->precio, 2), round($inven->ACTUALDL, 2), $tasa, $inven->CODIGO, $inven->DESCR, $inven->DUNIMEDD, $request->modo, $iva);
            */
            $detalle = (new PedidoDetalle)->createNew($pedido->id, $request->cantidad, round($request->precio, 2), round($inven->ACTUALDL, 2), $tasa, $inven->CODIGO, $inven->DESCR, $inven->DUNIMEDD, $request->modo, $inven->IMPUEST);

            $detalle = (new PedidoDetalle)->searchProduct($pedido->id, $inven->CODIGO);
        } else {
            $edit = (new PedidoDetalle)->updateItem($detalle->id, 'cantidad', $request->cantidad);
        }
        $user = (new User)->find($pedido->user_id);
        $seller = (new Vendedor)->where('email', $user->email)->first();
        $disp = 0;
        if ($seller) {
            $cdepos = $pedido->cdepos;
            $artdepos = (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->first();
            if ($artdepos) {
                $qty = $artdepos->RESERVA >= 0 ? $artdepos->RESERVA + $request->cantidad : $request->cantidad;
                //$qty += $request->cantidad;
                (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $qty]);
                $disp = $artdepos->EUNIDAD - $qty;
            }
        }

        return Response::json([
            'type' => 'success',
            'message' => 'Producto agregado...',
            'qty' => $disp,
            'pedido' => $pedido
        ], 200);
    }

    private function getTasa()
    {
        $todayTasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
        return $todayTasa ? $todayTasa->valor : 0;
    }

    public function updateEstatusOrder(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $pedido = (new Pedido)->where('id', $request->order_id)->first();
        if (!$pedido) {
            return Response::json([
                'type' => 'error',
                'message' => 'Pedido No Existe...'
            ], 200);
        }

        $pedido->estatus = $request->estatus;
        $pedido->save();

        $detalle = (new PedidoDetalle)->where('pedido_id', $pedido->id)->get();
        $cdepos = $pedido->cdepos;
        foreach ($detalle as $item) {
            $artdepos = (new ArtDepos)->where('CODIGO', $item->codigo_inven)->where('CDEPOS', $cdepos)->first();
            if ($artdepos && ($request->estatus == 'APROBADO' || $request->estatus == 'RECHAZADO')) {
                $eunidad = $request->estatus == 'APROBADO' ? $artdepos->EUNIDAD - $item->cantidad : $artdepos->EUNIDAD;
                $reserva = $artdepos->RESERVA - $item->cantidad;
                (new ArtDepos)->where('CODIGO', $item->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $reserva, 'EUNIDAD' => $eunidad]);
            }
        }


        $this->sendEstatusOrderEmail($pedido);

        if ($request->estatus == 'APROBADO') {
            //$this->sendEstatusOrderEmailAdmin($pedido);
        }


        return Response::json([
            'type' => 'success',
            'message' => 'Pedido Actualizado...',
            'data' => (new Vendedor)->getDataSeller($pedido->user_id),
        ], 200);
    }

    public function getSellerBalance(Request $request, $seller_id)
    {
        return Response::json([
            'type' => 'success',
            'data' => (new Vendedor)->getSellerBalance($seller_id)
        ], 200);
    }

    public function orderSavePayment(Request $request)
    {
        if (!$request->fecha) {
            return Response::json([
                'type' => 'error',
                'message' => 'Faltan datos'
            ], 200);
        }

        $payment = (new Pago)->create([
            'fecha' => $request->fecha,
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'seller_id' => $request->seller_id,
            'user_id' => auth()->user()->id,
        ]);

        if (is_array($request->payment_orders) && count($request->payment_orders) > 0) {
            foreach ($request->payment_orders as $order) {
                if (isset($order['pedido_id']) && isset($order['monto']) && $order['monto'] > 0) {
                    DB::connection('company')->table('pagos_pedidos')->insert([
                        'pago_id' => $payment->id,
                        'pedido_id' => $order['pedido_id'],
                        'monto' => $order['monto']
                    ]);
                }
            }
        }
        return Response::json([
            'type' => 'success',
            'text' => 'Pago Guardado...',
            'title' => 'Genial!',
            'data' => (new Vendedor)->getSellerBalance($request->seller_id),
            'payment' => $payment
        ], 200);
    }

    public function orderDeletePayment(Request $request)
    {
        $payment = (new Pago)->find($request->payment_id);
        (new PagoPedido)->where('pago_id', $request->payment_id)->delete();
        (new Pago)->where('id', $request->payment_id)->delete();

        return Response::json([
            'type' => 'success',
            'data' => (new Vendedor)->getSellerBalance(@$payment->seller_id)
        ], 200);
    }

    public function searchClient(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $client = (new OrderClient)->select('NOMBRE', 'TELEFONO', 'EMAIL')->where('RIF', $request->rif)->first();
        if (!$client) {
            $client = (new OrderClient)->select('NOMBRE', 'TELEFONO', 'EMAIL')->where('CODCLI', $request->rif)->first();
        }
        //$name = $client ? $client->NOMBRE : '';

        return Response::json([
            'type' => 'success',
            'data' => $client
        ], 200);
    }

    public function dropOrder(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $pedido = (new Pedido)->dropOrder();

        return redirect()->route('shoppingcart');
    }

    private function sendNotification($template = 'notificacion_pedido', $cliente = 'Dacabe', $pedido_id = null, $phone = '584129003985')
    {
        /*
        $response = file_get_contents('https://santiscodes.com/send-whatsapp?to=' . urlencode($phone) . 
           '&template=' . urlencode($template) . 
           '&cliente=' . urlencode($cliente) . 
           '&pedido_id=' . urlencode($pedido));
        */

        $url = 'https://santiscodes.com/send-whatsapp?to=' . $phone . '&template=' . $template . '&pedido_id=' . $pedido_id . '&cliente=' . $cliente;
        //$response = file_get_contents($url);

        /*
        return Response::json([
            'type' => 'success',
            'data' => $response
        ], 200);
        */
    }

    public function saveOrder(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $esClienteNuevo = $request->boolean('is_new_client');

        $pedido = (new Pedido)->searchPendingOrder();
        if (!$pedido) {
            $pedido = (new Pedido)->createNew('', $request->descripcion, $request->rif, auth()->user()->name, $request->telefono, $request->email, 'PENDIENTE', $request->observations, $request->conditions, $request->factura, $request->porc_retencion, $request->retencion, $request->cliageret, $request->codcli);
        } else {
            $update = (new Pedido)->updateItem($pedido->id, $request, 'PENDIENTE');
            $this->sendNotification('notificacion_pedido', urlencode($update->descripcion), $update->id, '584126202649');
            $this->sendNotification('notificacion_pedido', urlencode($update->descripcion), $update->id, '584129003985');
        }

        if ($pedido && $request->file('photo')) {
            $rif = $this->savePhotoRif($request, $pedido);
            if ($rif) {
                $pedido->rif_foto = $rif;
                $pedido->save();
            }
        }

        $detalle = (new PedidoDetalle)->searchOrderDetail($pedido->id);
        if ($detalle) {
            $alertaAsociacion = $this->getClienteAsociadoAOtroVendedor($request->rif, auth()->user()->email, $esClienteNuevo);

            if ($alertaAsociacion) {
                return Response::json([
                    'type' => 'success',
                    'title' => 'Pedido Guardado',
                    'text' => 'Pedido Guardado...',
                    'association_warning' => true,
                    'alert_icon' => 'warning',
                    'alert_title' => 'Atención',
                    'alert_html' => '<div style="font-size:1rem;line-height:1.5"><strong>El cliente ingresado está asociado al vendedor:</strong><br><span style="display:inline-block;margin-top:8px;padding:6px 12px;border-radius:999px;background:#fff4e5;color:#7a4b00;font-weight:700">' . e($alertaAsociacion['nombre_vendedor']) . '</span></div>',
                    'vendedor_asociado' => $alertaAsociacion['nombre_vendedor'],
                    'email_vendedor_asociado' => $alertaAsociacion['email_vendedor'],
                ], 200);
            }

            return Response::json([
                'type' => 'success',
                'text' => 'Pedido Guardado...',
                'title' => 'Genial!',
            ], 200);
        }
        return Response::json([
            'type' => 'error',
            'text' => 'Debe cargar productos al pedido...',
            'title' => 'Oopss!'
        ], 404);
    }

    private function getClienteAsociadoAOtroVendedor(?string $rif, ?string $emailVendedorActual, bool $esClienteNuevo = false): ?array
    {
        if (!$esClienteNuevo || !$rif || !$emailVendedorActual) {
            return null;
        }

        $rifNormalizado = strtoupper(trim($rif));
        $emailVendedorActual = strtolower(trim($emailVendedorActual));

        if ($rifNormalizado === '' || $emailVendedorActual === '') {
            return null;
        }

        $clienteExiste = (new OrderClient)
            ->whereRaw('UPPER(RIF) = ?', [$rifNormalizado])
            ->exists();

        if (!$clienteExiste) {
            return null;
        }

        $asociacion = ClienteVendedor::query()
            ->whereRaw('UPPER(rif) = ?', [$rifNormalizado])
            ->whereRaw('LOWER(email_vendedor) <> ?', [$emailVendedorActual])
            ->orderBy('id', 'desc')
            ->first();

        if (!$asociacion) {
            return null;
        }

        $usuarioVendedor = User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($asociacion->email_vendedor)])
            ->first(['name', 'last_name', 'email']);

        $nombreVendedor = trim((string) ($usuarioVendedor->name ?? '') . ' ' . (string) ($usuarioVendedor->last_name ?? ''));
        if ($nombreVendedor === '') {
            $nombreVendedor = $asociacion->email_vendedor;
        }

        return [
            'nombre_vendedor' => $nombreVendedor,
            'email_vendedor' => $asociacion->email_vendedor,
        ];
    }

    public function updateOrderProducts(Request $request)
    {
        //if (! hasOrderPermission()) 
        //    abort(403);

        $detalle = (new PedidoDetalle)->where('pedido_id', $request->order_id)->where('codigo_inven', $request->codigo_inven)->first();
        $retencion = 0;
        if ($detalle) {
            $pedido = (new Pedido)->find($request->order_id);
            if ($pedido) {
                $retencion = $pedido->porc_retencion;
            }
            $user = (new User)->find($pedido->user_id);
            $seller = (new Vendedor)->where('email', $user->email)->first();
            if ($seller) {
                $cdepos = $pedido->cdepos;
                $artdepos = (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->first();
                if ($artdepos) {
                    $qty = $artdepos->reserva > 0 ? $artdepos->reserva - $detalle->cantidad : 0;
                    //$artdepos->reserva = $qty + $request->cantidad;
                    //$artdepos->save();
                    $dif = $artdepos->RESERVA - $detalle->cantidad;
                    $qty = $request->cantidad;
                    (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $qty + $dif]);
                }
            }

            $detalle->cantidad = $request->cantidad;
            $detalle->cant_nota = $request->cantidad_nota;
            $detalle->precio_dolar = $request->precio_dolar;
            $detalle->save();

            $totales = (new PedidoDetalle)
                ->where('pedido_id', $request->order_id)
                ->selectRaw('SUM(cantidad * precio_dolar) as total_precio, SUM(cantidad) as total_cantidad, SUM(cantidad * precio_dolar * iva / 100) as total_iva')
                ->first();

            return Response::json([
                'type' => 'success',
                'text' => 'Pedido Guardado...',
                'title' => 'Genial!',
                'totales' => $totales,
                'data' => $detalle,
                'retencion' => $retencion
            ], 200);
        }
        return Response::json([
            'type' => 'error',
            'text' => 'Producto No Encontrado...',
            'title' => 'Oopss!'
        ], 404);
    }

    private function savePhotoRif($request)
    {
        return $this->uploadPhoto($request);
    }

    public function store(OrderInvenRequest $request)
    {
        if (!hasOrderPermission())
            abort(403);

        $order_inven = (new OrderInven)->createNew($request);

        return redirect()->route($this->module . '.index')
            ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if (!hasOrderPermission())
            abort(403);

        $route = $this->module . '.index';
        return view($this->module . '.create', compact(['route']));
    }

    public function update(OrderInvenRequest $request, $code)
    {
        if (!hasOrderPermission())
            abort(403);

        //$order_inven = (new OrderInven)->updateItem($code, $request);
        $order_inven = (new OrderInven)->getProduct($code);

        if ($order_inven && $request->file('photo')) {
            $this->savePhoto($request, $order_inven);
        }

        return redirect()->route($this->permission . '.index')
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
        if (!hasOrderPermission()) {
            abort(403);
        }

        $order_inven = (new OrderInven)->getData($code);

        $route = $this->permission . '.index';
        return view($this->module . '.edit', compact(['order_inven', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if (!hasOrderPermission())
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

        $pdf = Pdf::loadView($this->module . '.partials.print', compact('order_invens', 'today'));

        return $pdf->download('lista_unidades.pdf');
    }

    public function showPrintList()
    {
        $order_inven = new OrderInven();
        $order_invens = $order_inven->getData();
        $print = 1;

        $report_data = $order_inven->getReportConfig();

        return view($this->module . '.partials.print', compact(['order_invens', 'print', 'report_data']));
    }

    public function printOrder(Request $request, $order_id)
    {
        $modelo = new Pedido();
        $order = $modelo->getData($order_id);
        $client = null;
        if ($order->rif) {
            $client = (new OrderClient)->where('RIF', $order->rif)->first();
        }
        $print = 1;

        $report_data = $modelo->getReportConfig();

        return view('orders.partials.print_order', compact(['order', 'print', 'report_data', 'client']));
    }

    public function generatePdf($id)
    {
        $order = (new Pedido)->getData($id);
        if (!$order) {
            abort(404);
        }

        $print = null;

        $report_data = (new Pedido)->getReportConfig();
        $company = (new Company)->getMyCompany();
        $client = null;
        if ($order->rif) {
            $client = (new OrderClient)->where('RIF', $order->rif)->first();
        }

        $pdf = PDF::loadView($this->module . '.partials.pdf', compact(['order', 'print', 'report_data', 'company', 'client']));
        $pdf->setPaper('a4', 'portrait');

        $id = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        $ruta_pdf = storage_path('app/public/pedidos/') . 'Pedido_' . $id . '.pdf';
        $pdf->save($ruta_pdf)->download();

        return $pdf->download('pedido' . $id . '.pdf');
    }

    public function generateEmailPdf($id)
    {
        $order = (new Pedido)->getData($id);
        if (!$order) {
            abort(404);
        }

        if (!$order->email) {
            return Response::json([
                'type' => 'error',
                'title' => 'No se encontro destinatario!',
                'text' => 'El cliente no tiene email registrado...'
            ], 200);
        }

        $print = null;

        $report_data = (new Pedido)->getReportConfig();
        $company = (new Company)->getMyCompany();
        $client = null;
        if ($order->rif) {
            $client = (new OrderClient)->where('RIF', $order->rif)->first();
        }

        $data = $order->toArray();

        if ($order->estatus == 'APROBADO') {
            $pdf = PDF::loadView($this->module . '.partials.pdf', compact(['order', 'print', 'report_data', 'company', 'client']));
            $pdf->setPaper('a4', 'portrait');

            $ruta_pdf = storage_path('app/public/pedidos/') . 'Pedido_' . uniqid() . '.pdf';
            $pdf->save($ruta_pdf)->download();
            $data['estatus_message'] = 'nos complace notificarle que su pedido ha sido <b>APROBADO</b> con éxito!<br /><br /> Le invitamos a conocer los detalles de su pedido en el documento adjunto a este correo.';
            $data['ruta_pdf'] = $ruta_pdf;
        } else {
            $data['estatus_message'] = 'le informamos que su pedido lamentablemente <b>NO</b> ha sido APROBADO.<br /><br /> Le invitamos ha visualizar nuestras promociones y galeria de productos.';
        }

        $seller = (new User)->find($order->user_id);
        $data['seller'] = @$seller && $seller->email ? $seller->email : null;
        $data['company'] = $company;
        $data['email'] = $order->email;
        $data['name'] = $order->descripcion;
        $data['id'] = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $data['url'] = url('inicio');

        dispatch(new \App\Jobs\SendEmailInvoice($data));

        return Response::json([
            'type' => 'success',
            'title' => 'Pedido enviado!',
            'text' => 'El Pedido ha sido enviado por email satisfactoriamente.'
        ], 200);
    }

    private function sendEstatusOrderEmail($order)
    {
        if (!$order->email) {
            return Response::json([
                'type' => 'error',
                'title' => 'No se encontro destinatario!',
                'text' => 'El cliente no tiene email registrado...'
            ], 200);
        }

        $data = $order->toArray();

        $print = null;
        $report_data = (new Pedido)->getReportConfig();
        $company = (new Company)->getMyCompany();
        $ruta_pdf = null;
        if ($order->estatus == 'APROBADO') {
            $pdf = PDF::loadView($this->module . '.partials.pdf', compact(['order', 'print', 'report_data', 'company']));
            $pdf->setPaper('a4', 'portrait');

            $ruta_pdf = storage_path('app/public/pedidos/') . 'Pedido_' . uniqid() . '.pdf';
            $pdf->save($ruta_pdf)->download();
            $data['estatus_message'] = 'nos complace notificarle que su pedido ha sido <b>APROBADO</b> con éxito!<br /><br /> Le invitamos a conocer los detalles de su pedido en el documento adjunto a este correo.';
        } else {
            $data['estatus_message'] = 'le informamos que su pedido lamentablemente <b>NO</b> ha sido APROBADO.<br /><br /> Le invitamos ha visualizar nuestras promociones y galeria de productos.';
        }

        $seller = (new User)->find($order->user_id);
        $data['seller'] = @$seller && $seller->email ? $seller->email : null;
        $data['company'] = $company;
        $data['email'] = $order->email;
        $data['name'] = $order->descripcion;
        $data['id'] = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $data['url'] = url('inicio');
        $data['ruta_pdf'] = $ruta_pdf;

        dispatch(new \App\Jobs\SendEmailInvoice($data))->afterResponse();

        return Response::json([
            'type' => 'success',
            'title' => 'Pedido enviado!',
            'text' => 'El Pedido ha sido enviado por email satisfactoriamente.'
        ], 200);
    }

    private function sendEstatusOrderEmailAdmin($order)
    {
        $data = $order->toArray();
        $print = null;
        $report_data = (new Pedido)->getReportConfig();
        $company = (new Company)->getMyCompany();
        $ruta_pdf = null;
        $pdf = PDF::loadView($this->module . '.partials.pdf', compact(['order', 'print', 'report_data', 'company']));
        $pdf->setPaper('a4', 'portrait');

        $ruta_pdf = storage_path('app/public/pedidos/') . 'Pedido_' . uniqid() . '.pdf';
        $pdf->save($ruta_pdf)->download();
        $data['estatus_message'] = 'nos complace notificarle que tenemos un nuevo pedido ha sido <b>APROBADO</b> con éxito!<br /><br /> Le invitamos a conocer los detalles de su pedido en el documento adjunto a este correo.';

        $seller = (new User)->find($order->user_id);
        $data['seller'] = @$seller && $seller->email ? $seller->email : null;
        $data['company'] = $company;
        $data['email'] = 'facturaciondacabe@gmail.com';
        $data['name'] = 'Facturación DACABE';
        $data['client'] = $order->descripcion;
        $data['rif'] = $order->rif;
        $data['id'] = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $data['url'] = url('inicio');
        $data['ruta_pdf'] = $ruta_pdf;
        $data['rif_foto'] = $order->rif_foto;
        $data['observations'] = $order->observations;
        $data['conditions'] = $order->conditions;

        dispatch(new \App\Jobs\SendEmailInvoiceAdmin($data));

        return Response::json([
            'type' => 'success',
            'title' => 'Pedido enviado!',
            'text' => 'El Pedido ha sido enviado por email satisfactoriamente.'
        ], 200);
    }

    public function sellerBalancePrint(Request $request, $seller_id)
    {

        $modelo = new Vendedor();
        $balance = $modelo->getSellerBalance($seller_id);
        $print = 1;

        $report_data = $modelo->getReportConfig();

        return view('orders.partials.print_seller_balance', compact(['balance', 'print', 'report_data']));
    }

    public function generateSellerBalancePdf($id)
    {
        $order = (new Pedido)->getData($id);
        if (!$order) {
            abort(404);
        }

        $print = null;

        $report_data = (new Pedido)->getReportConfig();
        $company = (new Company)->getMyCompany();
        $client = null;
        if ($order->rif) {
            $client = (new OrderClient)->where('RIF', $order->rif)->first();
        }

        $pdf = PDF::loadView($this->module . '.partials.pdf', compact(['order', 'print', 'report_data', 'company', 'client']));
        $pdf->setPaper('a4', 'portrait');

        $id = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        $ruta_pdf = storage_path('app/public/pedidos/') . 'Pedido_' . $id . '.pdf';
        $pdf->save($ruta_pdf)->download();

        return $pdf->download('pedido' . $id . '.pdf');
    }


    public function verifyClient($id)
    {
        $pedido = (new Pedido)->where('id', $id)->first();
        if (!$pedido) {
            return Response::json([
                'type' => 'error',
                'message' => 'Pedido No Existe...'
            ], 200);
        }

        $pedido->cliente_verificado = 'SI';
        $pedido->save();

        return Response::json([
            'type' => 'success',
            'title' => 'Genial!',
            'message' => 'Pedido Actualizado...'
        ], 200);
    }

    public function getOrderPendingBySeller($seller_id)
    {
        $seller = (new Vendedor)->find($seller_id);
        $user = (new User)->where('email', $seller->email)->first();
        // Obtener todos los pedidos pendientes del vendedor
        // Obtener todos los pedidos del vendedor
        $orders = (new Pedido)
            ->where('user_id', $user->id)
            ->where('estatus', 'APROBADO')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'estatus']);

        // Agregar el total calculado a cada pedido
        foreach ($orders as $order) {
            $total = (new PedidoDetalle)
                ->where('pedido_id', $order->id)
                ->selectRaw('SUM(cantidad * precio_dolar) as total')
                ->value('total');
            $order->total = $total ?? 0;
        }

        // Para cada pedido, calcular el saldo pendiente cruzando con pagos_pedidos y pagos
        $pendingOrders = [];
        foreach ($orders as $order) {
            // Sumar todos los pagos asociados a este pedido
            $pagos = DB::connection('company')->table('pagos_pedidos')
                ->join('pagos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
                ->where('pagos_pedidos.pedido_id', $order->id)
                ->sum('pagos_pedidos.monto');

            $saldo = round($order->total - $pagos, 2);

            // Considerar pendiente si el saldo es mayor a 0.01 (para evitar problemas de redondeo)
            if ($saldo > 0.01) {
                $order->saldo_pendiente = number_format($saldo, 2, '.', '');
                $pendingOrders[] = $order;
            }
        }

        if (empty($pendingOrders)) {
            return Response::json([
                'type' => 'error',
                'message' => 'No hay pedidos pendientes...'
            ], 200);
        }

        return Response::json([
            'type' => 'success',
            'data' => $pendingOrders
        ], 200);
        if (!$order) {
            return Response::json([
                'type' => 'error',
                'message' => 'Pedido No Existe...'
            ], 200);
        }

        return Response::json([
            'type' => 'success',
            'data' => $order
        ], 200);
    }

    public function verifyClientSeniat($rif)
    {
        $response = file_get_contents('http://host2.ingenix21.com.ve:6080/cgi-bin/sinfonix_sql.pl?%5ERIF.wwm%20' . $rif);
        if ($response) {
            $respuestaString = mb_convert_encoding($response, 'UTF-8', 'UTF-8');
            // Decodificar la cadena JSON
            $datos = json_decode($respuestaString, true);
            // Verificar si la decodificación fue exitosa
            if (json_last_error() === JSON_ERROR_NONE) {
                // Aquí puedes trabajar con los datos decodificados
                //$cliente = response()->json($datos);
                //dd($cliente);
                if (isset($datos['nombre'])) {
                    // Acceder al campo 'nombre' de la respuesta
                    $nombre = $datos['nombre'];
                    $condicion = $datos['condicion'];
                    $retencion = $datos['retencion'];
                    return response()->json([
                        'nombre' => $nombre,
                        'condicion' => $condicion,
                        'retencion' => $retencion
                    ]);
                }
                return response()->json($datos);
            } else {
                // Manejar el error de decodificación
                return response()->json(['error' => 'Error al decodificar JSON'], 500);
            }
        } else {
            return response()->json(['error' => 'No se pudo obtener la respuesta del servicio'], 500);
        }
    }


    public function verifyClientSeniatNew(Request $request)
    {
        $url = 'http://contribuyente.seniat.gob.ve/BuscaRif/BuscaRif.jsp';

        // Inicializar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Obtener el HTML de la página
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) {
            return Response::json([
                'type' => 'error',
                'message' => 'No se pudo obtener la página de SENIAT.'
            ], 500);
        }

        // Usar DOMDocument para parsear el HTML
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Obtener el formulario principal
        $form = $xpath->query("//form[@name='consulta']")->item(0);

        $inputs = [];
        $captcha_img = '';
        if ($form) {
            // Obtener solo p_rif y codigo
            foreach ($xpath->query(".//input", $form) as $input) {
                $name = $input->getAttribute('name');
                if (in_array($name, ['p_rif', 'codigo'])) {
                    $inputs[] = [
                        'name' => $name,
                        'type' => $input->getAttribute('type'),
                        'value' => $input->getAttribute('value')
                    ];
                }
            }

            // Obtener el captcha (img)
            $img = $xpath->query(".//img[contains(@src, 'Captcha')]", $form)->item(0);
            if ($img) {
                $captcha_src = $img->getAttribute('src');
                $captcha_img = 'http://contribuyente.seniat.gob.ve/BuscaRif/' . $captcha_src;
            }
        }

        // Construir el HTML para la modal
        $modalHtml = '<form id="seniatForm">';
        foreach ($inputs as $input) {
            $modalHtml .= '<div class="mb-2"><label>' . $input['name'] . '</label><input type="' . $input['type'] . '" name="' . $input['name'] . '" value="' . $input['value'] . '" class="form-control"></div>';
        }
        if ($captcha_img) {
            $modalHtml .= '<div class="mb-2"><label>Captcha</label><br><img src="' . $captcha_img . '" alt="captcha"></div>';
            $modalHtml .= '<div class="mb-2"><input type="text" name="captcha" class="form-control" placeholder="Ingrese el captcha"></div>';
        }
        // Botón Buscar
        $modalHtml .= '<button type="submit" class="btn btn-primary">Buscar</button>';
        $modalHtml .= '</form>';

        return Response::json([
            'type' => 'success',
            'html' => $modalHtml
        ], 200);
    }
}
