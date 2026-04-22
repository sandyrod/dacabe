<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\PermissionCreateRequest;
use App\Http\Requests\PermissionEditRequest;
use App\Permission;
use App\Traits\PermissionTrait;

use Illuminate\Support\Facades\Response;

class PermissionsController extends Controller
{
    use PermissionTrait;

    private $permission;
    private $module;
    private $validate_create;
    private $validate_edit;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'permission';
        $this->module = 'permissions';
        $this->validate_create = 'PermissionCreate';
        $this->validate_edit = 'PermissionEdit';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
    	$permissions = Permission::get();
     
        return (requestAjaxOrJson($request, $permissions)) ? $this->getJsonOrDatatableResponse($request, $permissions) : view($this->module.'.index');
    }

    public function store(PermissionCreateRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $permission = Permission::create($request->except('permissions', 'permission_id'));
        
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$permission->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        $permissions = Permission::get(['id', 'display_name', 'name']);
        $validate = $this->validate_create;
        return view($this->module.'.create', compact(['route', 'validate', 'permissions']));
    }

    public function update(PermissionEditRequest $request, Permission $permission)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $permission->fill($request->except(['permission_id']))->save();
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$permission->name.' ha sido modificado satisfactoriamente');
    }

    public function edit(Permission $permission)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        $validate = $this->validate_edit;

        return view($this->module.'.edit', compact(['permission', 'route', 'validate']));
    }

    public function destroy(Permission $permission)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $permission->name;

        $permission->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function showPrintList()
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $permission = new Permission();
        $permissions = $permission->getData();
        $print = 1;
        
        $report_data = $permission->getReportConfig();

        return view($this->module.'.partials.print', compact(['permissions', 'print', 'report_data']));
    }
}
