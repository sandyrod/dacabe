<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\LandingRequest;
use App\Models\{Landing, Theme, Company};
use App\Traits\LandingsTrait;

use App\Http\Resources\LandingsResource;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class LandingsController extends Controller
{
    use LandingsTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'landings';
        $this->module = 'landings';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $landings = (new Landing)->getData();
     
        if (requestAjaxOrJson($request, $landings)) {
            return $this->getJsonOrDatatableResponse($request, $landings);
        }

        return view($this->module.'.index');
    }

    public function store(LandingRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $landing = Landing::create($request->all());

        if (requestAjaxOrJson($request))
            return new LandingsResource($landing);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$landing->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            abort(403);
        }
        $companies = (new Company)->getData();
        $themes = (new Theme)->getData();

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route', 'companies', 'themes']));
    }

    public function update(LandingRequest $request, Landing $landing)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }
        
        $prepared_request = $request->except(['main_logo', 'footer_logo']) ;
        $landing->fill($prepared_request)->save();

        if ($request->file('main_logo')) { 
            $this->savePhoto($request, $landing, 'main_logo');
        }

        if ($request->file('footer_logo')) { 
            $this->savePhoto($request, $landing, 'footer_logo');
        }

        if (requestAjaxOrJson($request)) {
            return new LandingsResource($landing);
        }
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$landing->name.' ha sido modificado satisfactoriamente');
    }

    private function savePhoto($request, Landing $landing, $save_in)
    {
        $file_name = $this->uploadPhoto($request->$save_in);
        \Log::info($file_name);
        $landing->fill([$save_in=>$file_name])->save();        
    }

    private function uploadPhoto($file)
    {
        $name = Storage::disk('local')->put('public/landings', $file);
        return substr($name, 16); 
    }

    public function edit(Landing $landing)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }
        $companies = (new Company)->getData();
        $themes = (new Theme)->getData();

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['landing', 'route', 'companies', 'themes']));
    }

    public function destroy(Landing $landing)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $landing->name;
        $landing->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Landing $landing)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new LandingsResource($landing);
    }

    public function showPdfList()
    {
        $landings = (new Landing)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('landings', 'today'));
 
        return $pdf->download('lista_temas.pdf');
    }

     public function showPrintList()
    {
        $landing = new Landing();
        $landings = $landing->getData();
        $print = 1;
        
        $report_data = $landing->getReportConfig();

        return view($this->module.'.partials.print', compact(['landings', 'print', 'report_data']));
    }


}
