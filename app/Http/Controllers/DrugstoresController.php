<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\DrugstoreRequest;
use App\Models\Drugstore;
use App\Traits\DrugstoreTrait;

use App\Http\Resources\DrugstoreResource;

use Illuminate\Support\Facades\Response;

class DrugstoresController extends Controller
{
    use DrugstoreTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'drugstores';
        $this->module = 'drugstores';
        $this->paginate = 10;
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $drugstores = (new Drugstore)->getData();
     
        if (requestAjaxOrJson($request, $drugstores)) 
            return $this->getJsonOrDatatableResponse($request, $drugstores);

        return view($this->module.'.index');
    }

    public function store(DrugstoreRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $drugstore = Drugstore::create($request->all());

        if (requestAjaxOrJson($request))
            return new DrugstoreResource($drugstore);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$drugstore->name.' ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function update(DrugstoreRequest $request, Drugstore $drugstore)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);
        
        $drugstore->fill($request->all())->save();

        if (requestAjaxOrJson($request))
            return new DrugstoreResource($drugstore);
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$drugstore->name.' ha sido modificado satisfactoriamente');
    }

    public function edit(Drugstore $drugstore)
    {
        if ( ! hasPermission('edit-'.$this->permission) )
            abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['drugstore', 'route']));
    }

    public function destroy(Drugstore $drugstore)
    {
        if ( ! hasPermission('delete-'.$this->permission) )
            abort(403);

        $name = $drugstore->name;
        $drugstore->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Drugstore $drugstore)
    {
        if ( ! hasPermission($this->permission) )
            return notPermissionResponse($module);

        return  new DrugstoreResource($drugstore);
    }

    public function showPdfList()
    {
        $drugstore = new Drugstore();
        $drugstores = $drugstore->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('drugstores', 'today'));
 
        return $pdf->download('lista_comandos.pdf');
    }

     public function showPrintList()
    {
        $drugstore = new Drugstore();
        $drugstores = $drugstore->getData();
        $print = 1;
        
        $report_data = $drugstore->getReportConfig();

        return view($this->module.'.partials.print', compact(['drugstores', 'print', 'report_data']));
    }


}
