<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\UnimedRequest;
use App\Models\Unimed;
use App\Traits\UnimedTrait;

use Illuminate\Support\Facades\Response;

class UnimedController extends Controller
{
    use UnimedTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'unimed';
        $this->module = 'unimed';
    }

    public function index(Request $request)
    {
        if (! hasOrderPermission()) 
            abort(403);
     
        $unimeds = (new Unimed)->getData();
     
        return (requestAjaxOrJson($request, $unimeds)) ? $this->getJsonOrDatatableResponse($request, $unimeds) : view($this->module.'.index');
    }

    public function store(UnimedRequest $request)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $unimed = (new Unimed)->createNew($request);
       
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

    public function update(UnimedRequest $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);
        
        $unimed = (new Unimed)->updateItem($code, $request);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $unimed = (new Unimed)->getData($code);
        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['unimed', 'route']));
    }

    public function destroy(Request $request, $code)
    {
        if (! hasOrderPermission()) 
            abort(403);

        $unimed = (new Unimed)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $unimeds = (new Unimed)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('unimeds', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $unimed = new Unimed();
        $unimeds = $unimed->getData();
        $print = 1;
        
        $report_data = $unimed->getReportConfig();

        return view($this->module.'.partials.print', compact(['unimeds', 'print', 'report_data']));
    }


}
