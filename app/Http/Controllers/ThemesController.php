<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\ThemeRequest;
use App\Models\Theme;
use App\Traits\ThemeTrait;

use App\Http\Resources\ThemeResource;

use Illuminate\Support\Facades\Response;

class ThemesController extends Controller
{
    use ThemeTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'themes';
        $this->module = 'themes';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $themes = (new Theme)->getData();
     
        if (requestAjaxOrJson($request, $themes)) {
            return $this->getJsonOrDatatableResponse($request, $themes);
        }

        return view($this->module.'.index');
    }

    public function store(ThemeRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $theme = Theme::create($request->all());

        if (requestAjaxOrJson($request))
            return new ThemeResource($theme);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$theme->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(ThemeRequest $request, Theme $theme)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);
        
        $theme->fill($request->all())->save();

        if (requestAjaxOrJson($request))
            return new ThemeResource($theme);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$theme->name.' ha sido modificado satisfactoriamente');
    }

    public function edit(Theme $theme)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['theme', 'route']));
    }

    public function destroy(Theme $theme)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $theme->name;
        $theme->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Theme $theme)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new ThemeResource($theme);
    }

    public function showPdfList()
    {
        $themes = (new Theme)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('themes', 'today'));
 
        return $pdf->download('lista_temas.pdf');
    }

     public function showPrintList()
    {
        $theme = new Theme();
        $themes = $theme->getData();
        $print = 1;
        
        $report_data = $theme->getReportConfig();

        return view($this->module.'.partials.print', compact(['themes', 'print', 'report_data']));
    }


}
