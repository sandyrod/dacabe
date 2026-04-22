<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\CompanyRequest;
use App\User;
use App\Models\{Module, Serial, Ftp, Drugstore, Download, FtpInvoice};
use App\Models\Company;
use App\Models\CompanyStatus;
use App\Traits\CompanyTrait;

use App\Http\Resources\CompanyResource;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Storage;


class CompaniesController extends Controller
{
    use CompanyTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        //$this->middleware('auth');
        $this->permission = 'company';
        $this->module = 'companies';
        $this->paginate = 10;
    }


    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($request);
        
        if (requestAjaxOrJson($request))
            return $this->getJsonOrDatatableResponse($request);
        
        $status = CompanyStatus::pluck('name', 'id');
        return view($this->module.'.index', compact(['status']));
    }

    public function store(CompanyRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            return notPermissionResponse($request);

        $company = new Company([
            'name' => $request->name,
            'code' => $request->code,
            'url' => $request->url,
            'phone' => $request->phone,
            'db_name' => $request->db_name,
            'location' => $request->location
        ]);
        
        $company->save();

        if ($request->file('file')) {
            $this->savePhoto($request, $company);
        }
       
        if (requestAjaxOrJson($request))
            return new CompanyResource($company);

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$company->name.' ha sido creado satisfactoriamente');
    }

    public function update(CompanyRequest $request, Company $company)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);
        
        $company->update($request->only(['name', 'email', 'code', 'url', 'phone', 'location', 'status', 'db_name']));

        if ($request->file('file')) {
            $this->savePhoto($request, $company);
        }

        if (requestAjaxOrJson($request))
            return new CompanyResource($company);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$company->name.' ha sido modificado satisfactoriamente');
    }

    private function savePhoto($request, $company)
    {
        $file_name = $this->uploadPhoto($request);
        $company->fill(['logo' => $file_name])->save();
    }

    private function uploadPhoto($request)
    {
        $name = $request->file('file')->getClientOriginalName();
        $ext = substr($name, strlen($name)-4);
        $ext = substr($ext, 0, 1) == '.' ? $ext : '.' . $ext;
        $ext = $ext == '.jpg' ? '.jpeg' : $ext;
        //$path = Storage::disk('public')->put('users', $request->file('file'));    
        //return pathinfo($path)['filename'].$ext;

        $name = Storage::disk('local')->put('public/logos', $request->file('file'));
        return substr($name, 13); 
    }

    public function destroy(Company $company)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            return notPermissionResponse($request);

        if (User::where('company_id', $company->id)->first())
            return Response::json([
                'type' => 'error',
                'title' => 'No se puede Eliminar',
                'text' => 'Existen Usuarios asociados a esta empresa'
            ], 201);

        $name = $company->name;
        $company->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function changeStatus(Request $request, $company_id)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);

        $company = Company::find($company_id);
        if (! $company)
            return Response::json([
                'type' => 'error',
                'title' => 'Registro No Encontrado',
                'text' => 'El registro ha sido encontrado'
            ], 404);

        $company->company_status_id = $request->id;
        $company->save();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Actualizado!',
            'text' => 'El registro '.$company->name . ' ha sido actualizado satisfactoriamente.'
        ], 200);
    }

    public function show(Company $company)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($company);

        return  new CompanyResource($company);
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function edit(Company $company)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['company', 'route']));
    }

    public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $company = new Company();
        $companies = $company->getData();
        $print = 1;
        
        $report_data = $company->getReportConfig();

        return view($this->module.'.partials.print', compact(['companies', 'print', 'report_data']));
    }

    public function getPermissions(Request $request, $company_id)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);

        $module = new Module();
        $permissions = $module->getPermissions($company_id);

        return Response::json([
            'type' => 'success',
            'permissions' => $permissions
        ], 200);
    }

    public function setPermissions(Request $request)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            return notPermissionResponse($request);

        $company = Company::find($request->company_id);
        if (! $company)
            return Response::json([
                'type' => 'error',
                'title' => 'Registro No Encontrado',
                'text' => 'El registro ha sido encontrado'
            ], 404);

        $module = new Module();
        $permissions = $module->updatePermissions($request->module_id, $request->company_id, $request->expired_at, $request->uses);
        
        return Response::json([
            'type' => 'success',
            'title' => 'Registro Actualizado!',
            'text' => 'El permiso para '.$company->name . ' ha sido actualizado satisfactoriamente.',
            'permissions' => $permissions
        ], 200);
    }

    public function getCompanyIndicators()
    {
        return Response::json([
            'type' => 'success',
            'companies' => (new Company)->count(),
            'users' => (new User)->count(),
            'pharmacies' => (new Ftp)->count(),
            'drugstores' => (new Drugstore)->count(),
            'serials' => (new Serial)->sum('quantity'),
            'modules' => (new Module)->count(),
        ], 200);
    }

    public function getFarmaxDownloads()
    {
        return Response::json([
            'type' => 'success',
            'ftp_downloads' => (new FtpInvoice)
                            ->selectRaw('count(ftp_invoices.id) as n, companies.name')
                            ->join('companies', 'companies.id', 'company_id')
                            ->groupBy('company_id')
                            ->get(),
        ], 200);
    }


}
