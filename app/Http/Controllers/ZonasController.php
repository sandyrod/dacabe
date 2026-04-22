<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ZonaRequest;
use App\Models\{Zona};
use App\Traits\ZonaTrait;

use Illuminate\Support\Facades\Response;

class ZonasController extends Controller
{
    use ZonaTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'zonas';
        $this->module = 'zonas';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $zonas = (new Zona)->getData();
     
        return (requestAjaxOrJson($request, $zonas)) ? $this->getJsonOrDatatableResponse($request, $zonas) : view($this->module.'.index');
    }

    public function store(ZonaRequest $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $zona = (new Zona)->createNew($request);
       
        return redirect()->route($this->permission.'.index')->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $route = $this->permission.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(ZonaRequest $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
        
        $zona = (new Zona)->updateItem($code, $request);
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $zona = (new Zona)->getData($code);
        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['zona', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $zona = (new Zona)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $zonas = (new Zona)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('zonas', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $zona = new Zona();
        $zonas = $zona->getData();
        $print = 1;
        
        $report_data = $zona->getReportConfig();

        return view($this->module.'.partials.print', compact(['zonas', 'print', 'report_data']));
    }


}
