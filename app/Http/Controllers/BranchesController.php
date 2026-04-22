<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\BranchRequest;
use App\Models\{Branch};
use App\Traits\BranchTrait;

use DB;
use Illuminate\Support\Facades\Response;

class BranchesController extends Controller
{
    use BranchTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'branches';
        $this->module = 'branches';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) ) {
            abort(403);
        }
     
        $branches = (new Branch)->getData();
     
        return (requestAjaxOrJson($request, $branches)) ? $this->getJsonOrDatatableResponse($request, $branches) : view($this->module.'.index');
    }

    public function store(BranchRequest $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $branch = Branch::create($request->all());
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function showStatistics()
    {
        $route = 'inicio';
        return view($this->module.'.statistics', compact(['route']));
    }

    public function create()
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $route = $this->permission.'.index';

        return view($this->module.'.create', compact(['route']));
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
        
        $branch->fill($request->all())->save();
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, Branch $branch)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $route = $this->permission.'.index';
        return view($this->module.'.edit', compact(['branch', 'route']));
    }

    public function destroy(Request $request, Branch $branch)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $branch->delete(); 

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $branches = (new Branch)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('branches', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

     public function showPrintList()
    {
        $branch = new Branch();
        $branches = $branch->getData();
        $print = 1;
        
        $report_data = $branch->getReportConfig();

        return view($this->module.'.partials.print', compact(['branches', 'print', 'report_data']));
    }


}
