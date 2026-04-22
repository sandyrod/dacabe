<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleEditRequest;
use App\Role;
use App\Permission;
use App\Traits\RoleTrait;

use Illuminate\Support\Facades\Response;

class RolesController extends Controller
{
    use RoleTrait;

    private $permission;
    private $module;
    private $validate_create;
    private $validate_edit;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'role';
        $this->module = 'roles';
        $this->validate_create = 'RoleCreate';
        $this->validate_edit = 'RoleEdit';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
    	$roles = Role::get();
     
        return (requestAjaxOrJson($request, $roles)) ? $this->getJsonOrDatatableResponse($request, $roles) : view($this->module.'.index');
    }

    public function store(RoleCreateRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $role = Role::create($request->except('permissions', 'role_id'));
        $permissions = ($request->permissions) ? $request->permissions : [];
        $role->syncPermissions($permissions);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$role->name.' ha sido creado satisfactoriamente');
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

    public function update(RoleEditRequest $request, Role $role)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $role->fill($request->except('permissions', 'role_id'))->save();

        $role->syncPermissions($request->permissions);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$role->name.' ha sido modificado satisfactoriamente');
    }

    public function edit(Role $role)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        $validate = $this->validate_edit;
        $permission = new Permission();
        $permissions = $permission->getRolePermissions($role->id);
        return view($this->module.'.edit', compact(['role', 'route', 'validate', 'permissions']));
    }

    public function destroy(Role $role)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $role->name;

        $role->delete();

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

        $role = new Role();
        $roles = $role->getData();
        $print = 1;
        
        $report_data = $role->getReportConfig();

        return view($this->module.'.partials.print', compact(['roles', 'print', 'report_data']));
    }

    public function setActiveRole(Request $request)
    {
        $roleId = $request->role_id;
        $user = Auth::user();

        // Verificar que el usuario tenga el rol asignado
        if ($user->roles()->where('role_id', $roleId)->exists()) {
            $role = Role::find($roleId);
            session(['active_role_id' => $role->id]);
            session(['active_role_name' => $role->display_name]);

            return redirect()->route('inicio')->with('info', 'Has cambiado al rol: ' . $role->display_name);
        }

        return redirect()->back()->with('error', 'No tienes permisos para este rol.');
    }

}
