<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\DptoRequest;
use App\Models\Dpto;
use App\Traits\DptoTrait;

use Illuminate\Support\Facades\Response;

class DptoController extends Controller
{
    use DptoTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'dpto';
        $this->module = 'dpto';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        $dptos = (new Dpto)->getData();
     
        return (requestAjaxOrJson($request, $dptos)) ? $this->getJsonOrDatatableResponse($request, $dptos) : view($this->module.'.index');
    }

    public function store(DptoRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $dpto = (new Dpto)->createNew($request);
       
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

    public function update(DptoRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $dpto = (new Dpto)->updateItem($code, $request);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $dpto = (new Dpto)->getData($code);
        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['dpto', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $dpto = (new Dpto)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $dptos = (new Dpto)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('dptos', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $dpto = new Dpto();
        $dptos = $dpto->getData();
        $print = 1;
        
        $report_data = $dpto->getReportConfig();

        return view($this->module.'.partials.print', compact(['dptos', 'print', 'report_data']));
    }


}
