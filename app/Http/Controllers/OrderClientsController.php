<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\OrderClientRequest;
use App\Models\OrderClient;
use App\Traits\OrderClientTrait;

use Illuminate\Support\Facades\Response;

class OrderClientsController extends Controller
{
    use OrderClientTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'order-clients';
        $this->module = 'order_clients';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) {
            abort(403);
        }
     
        $order_clients = (new OrderClient)->getData();
     
        return (requestAjaxOrJson($request, $order_clients)) ? $this->getJsonOrDatatableResponse($request, $order_clients) : view($this->module.'.index');
    }

    public function store(OrderClientRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $order_client = (new OrderClient)->createNew($request);
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if (! hasOrderPermission()) 
            abort(403);

        $route = $this->permission.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(OrderClientRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $order_client = (new OrderClient)->updateItem($code, $request);
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $order_client = (new OrderClient)->getData($code);
        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['order_client', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $order_client = (new OrderClient)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $order_clients = (new OrderClient)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('order_clients', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $order_client = new OrderClient();
        $order_clients = $order_client->getData();
        $print = 1;
        
        $report_data = $order_client->getReportConfig();

        return view($this->module.'.partials.print', compact(['order_clients', 'print', 'report_data']));
    }


}
