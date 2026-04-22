<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ModuleRequest;
use App\Models\Module;
use App\Models\Company;
use App\Traits\ModuleTrait;

use App\Http\Resources\ModuleResource;

use Illuminate\Support\Facades\Response;

class ModulesController extends Controller
{
    use ModuleTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'module';
        $this->module = 'modules';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($request);
        
        if (requestAjaxOrJson($request))
            return $this->getJsonOrDatatableResponse($request);
        
        return view($this->module.'.index');
    }

    public function getCompanyPermissions(Request $request)
    {
        //if ( ! hasPermission($this->permission) )
        //    return notPermissionResponse($request);
        
        $model = new Module();
        return  Response::json([
            'permissions' => $model->getPermissions(Auth::user()->company->id), 
            'company' => Company::select('company_status_id')->find(Auth::user()->company->id)
        ]);
    }


    public function store(ModuleRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            return notPermissionResponse($request);

        $module = new Module([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'url' => $request->url,
            'button_text' => $request->button_text
        ]);
        
        $module->save();
       
        if (requestAjaxOrJson($request))
            return new ModuleResource($module);

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$module->name.' ha sido creado satisfactoriamente');
    }


    public function update(ModuleRequest $request, Module $module)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);
        
        $module->update($request->all());

        if (requestAjaxOrJson($request))
            return new ModuleResource($module);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$module->name.' ha sido modificado satisfactoriamente');
    }

    public function destroy(Module $module)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            return notPermissionResponse($request);

        $name = $module->name;
        $module->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Module $module)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new ModuleResource($module);
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function edit(Module $module)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['module', 'route']));
    }

    public function showPdfList()
    {
        $module = new Module();
        $modules = $module->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('modules', 'today'));
 
        return $pdf->download('lista_modulos.pdf');
    }

     public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $module = new Module();
        $modules = $module->getData();
        $print = 1;
        
        $report_data = $module->getReportConfig();

        return view($this->module.'.partials.print', compact(['modules', 'print', 'report_data']));
    }

}
