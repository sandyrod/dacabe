<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\CompanyStatusRequest;
use App\User;
use App\Models\Company;
use App\Models\CompanyStatus;
use App\Traits\CompanyStatusTrait;

use App\Http\Resources\CompanyStatusResource;
use Illuminate\Support\Facades\Response;

class CompanyStatusController extends Controller
{
    use CompanyStatusTrait;

    private $permission;
    private $module;
    private $route;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'company-status';
        $this->module = 'company-status';
        $this->dir = 'company_status';
        $this->paginate = 10;
    }


    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($request);
        
        if (requestAjaxOrJson($request))
            return $this->getJsonOrDatatableResponse($request);
        
        return view($this->dir.'.index');
    }

    public function store(CompanyStatusRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            return notPermissionResponse($request);

        $company_status = new CompanyStatus([
            'name' => $request->name,
            'description' => $request->description
        ]);
        
        $company_status->save();
       
        if (requestAjaxOrJson($request))
            return new CompanyStatusResource($company_status);

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$company_status->name.' ha sido creado satisfactoriamente');
    }

    public function update(CompanyStatusRequest $request, CompanyStatus $company_status)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);
        
        $company_status->update($request->only(['name', 'description']));

        if (requestAjaxOrJson($request))
            return new CompanyStatusResource($company_status);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$company_status->name.' ha sido modificado satisfactoriamente');
    }

    public function destroy(CompanyStatus $company_status)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            return notPermissionResponse($request);

        if (Company::where('company_status_id', $company_status->id)->first())
            return Response::json([
                'type' => 'error',
                'title' => 'No se puede Eliminar',
                'text' => 'Existen Empresas asociados a este status'
            ], 201);

        $name = $company_status->name;
        $company_status->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(CompanyStatus $company_status)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($company_status);

        return new CompanyStatusResource($company_status);
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->dir.'.create', compact(['route']));
    }

    public function edit(CompanyStatus $company_status)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->dir.'.edit', compact(['company_status', 'route']));
    }

    public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $company_status = new CompanyStatus();
        $company_status = $company_status->getData();
        $print = 1;
        
        $report_data = [
            'title' => 'Listado de Estatus de Empresas', 
            'company' => Auth::user()->company
        ];//$company_status->getReportConfig();

        return view($this->dir.'.partials.print', compact(['company_status', 'print', 'report_data']));
    }


}
