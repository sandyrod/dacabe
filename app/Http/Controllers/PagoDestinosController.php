<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\PagoDestinoRequest;
use App\Models\PagoDestino;

use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\PagoDestinosTrait;

class PagoDestinosController extends Controller
{
    use PagoDestinosTrait;
    
    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'pago_destinos';
        $this->module = 'pago_destinos';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        $pago_destinos = (new PagoDestino)->getData();
     
        return (requestAjaxOrJson($request, $pago_destinos)) ? $this->getJsonOrDatatableResponse($request, $pago_destinos) : view($this->module.'.index');
    }

    public function store(PagoDestinoRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        if ($request->nombre) {
            PagoDestino::create([
                'nombre' => strtoupper($request->nombre),
                'descripcion' => $request->descripcion,
            ]);
        }
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

    public function update(PagoDestinoRequest $request, PagoDestino $pago_destino)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $pago_destino = PagoDestino::findOrFail($request->id);
        $pago_destino->update([
            'nombre' => strtoupper($request->nombre),
            'descripcion' => $request->descripcion,
        ]);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $id)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $pago_destino = (new PagoDestino)->getData($id);
        $route = $this->module.'.index';
        
        return view($this->module.'.edit', compact(['pago_destino', 'route']));
    }

    public function destroy(Request $request, $pago_destino_id)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $pago_destino = PagoDestino::findOrFail($pago_destino_id);
        $pago_destino->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function showPdfList()
    {
        $pago_destinos = (new PagoDestino)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = Pdf::loadView($this->module.'.partials.print', compact('pago_destinos', 'today'));
 
        return $pdf->download('lista_destinos_pago.pdf');
    }

    public function showPrintList()
    {
        $pago_destino = new PagoDestino();
        $pago_destinos = $pago_destino->getData();
        $print = 1;
        
        $report_data = $pago_destino->getReportConfig();

        return view($this->module.'.partials.print', compact(['pago_destinos', 'print', 'report_data']));
    }
}
