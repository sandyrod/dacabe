<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\TipprodRequest;
use App\Models\Tipprod;
use App\Traits\TipprodTrait;

use Illuminate\Support\Facades\Response;

class TipprodController extends Controller
{
    use TipprodTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'tipprod';
        $this->module = 'tipprod';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        $tipprods = (new Tipprod)->getData();
     
        return (requestAjaxOrJson($request, $tipprods)) ? $this->getJsonOrDatatableResponse($request, $tipprods) : view($this->module.'.index');
    }

    public function store(TipprodRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $tipprod = (new Tipprod)->createNew($request);
       
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

    public function update(TipprodRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $tipprod = (new Tipprod)->updateItem($code, $request);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        //$tipprod = (new Tipprod)->getData($code);
        $tipprod = (new Tipprod)->where('CTIPPROD', $code)->first();
        $route = $this->module.'.index';

        return view($this->module.'.edit', compact(['tipprod', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $tipprod = (new Tipprod)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $tipprods = (new Tipprod)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('tipprods', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $tipprod = new Tipprod();
        $tipprods = $tipprod->getData();
        $print = 1;
        
        $report_data = $tipprod->getReportConfig();

        return view($this->module.'.partials.print', compact(['tipprods', 'print', 'report_data']));
    }


}
