<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\DescuentoRequest;
use App\Models\Descuento;
use App\Traits\DescuentoTrait;

use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DescuentosController extends Controller
{
    use DescuentoTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'descuentos';
        $this->module = 'descuentos';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        $descuentos = (new Descuento)->getData();
     
        return (requestAjaxOrJson($request, $descuentos)) ? $this->getJsonOrDatatableResponse($request, $descuentos) : view($this->module.'.index');
    }

    public function store(DescuentoRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        if ($request->nombre && $request->porcentaje) {
            Descuento::create([
                'nombre' => strtoupper($request->nombre),
                'porcentaje' => $request->porcentaje,
                'dias_despacho' => $request->dias_despacho,
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

    public function update(DescuentoRequest $request, Descuento $descuento)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $descuento = Descuento::findOrFail($request->id);
        $descuento->update([
            'nombre' => strtoupper($request->nombre),
            'porcentaje' => $request->porcentaje,
            'dias_despacho' => $request->dias_despacho,
        ]);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $id)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $descuento = (new Descuento)->getData($id);
        $route = $this->module.'.index';
        
        return view($this->module.'.edit', compact(['descuento', 'route']));
    }

    public function destroy(Request $request, $descuento_id)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $descuento = Descuento::findOrFail($descuento_id);
        $descuento->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $descuentos = (new Descuento)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = Pdf::loadView($this->module.'.partials.print', compact('dptos', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $dpto = new Descuento();
        $descuentos = $dpto->getData();
        $print = 1;
        
        $report_data = $dpto->getReportConfig();

        return view($this->module.'.partials.print', compact(['descuentos', 'print', 'report_data']));
    }


}
