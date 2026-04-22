<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\InvenRequest;
use App\User;
use App\Models\Module;
use App\Models\{Moviventas};
use App\Traits\MoviventasTrait;

use App\Http\Resources\InvenResource;
use Illuminate\Support\Facades\Response;

class MoviventasController extends Controller
{
    use MoviventasTrait;

    private $permission;
    private $module;
    private $paginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'moviventas';
        $this->module = 'moviventas';
        $this->paginate = 10;
    }


    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) ) {
            return notPermissionResponse($request);
        }
        
        //if (requestAjaxOrJson($request)) {
            return $this->getJsonOrDatatableResponse($request);
        //}
        
        return view($this->module.'.index');
    }

    public function store(InvenRequest $request)
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            return notPermissionResponse($request);
        }
        /*
        $inven = new Company([
            'name' => $request->name,
            'code' => $request->code,
            'url' => $request->url,
            'logo' => $request->logo,
            'phone' => $request->phone,
            'location' => $request->location
        ]);        
        $inven->save();
        */
        $inven = Inven::create($request->all());
       
        //if (requestAjaxOrJson($request)) {
            return new InvenResource($inven);
        //}

        return redirect()->route($this->module.'.index')
                ->with('info', 'El registro '.$inven->descr.' ha sido creado satisfactoriamente');
    }

    public function storeMasive(Request $request)
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            return notPermissionResponse($request);
        }
        dd($request);
        foreach($request->inven as $item) {
            if ($item['codigo']) {
                $moviventa = (new Moviventas)
                    ->where('company_id', Auth::user()->company_id)
                    ->where('nro', $item['nro'])
                    ->where('codigo', $item['codigo'])
                    ->first();

                if ($moviventa) {
                    //$moviventa->cantelim = $item['cantelim'];
                    $moviventa->nrodev = $item['nrodev'];
                    $moviventa->devolver = $item['devolver'];
                    $moviventa->cantdev = $item['cantdev'];

                    $moviventa->save();
                } else {
                    $moviventa = Moviventas::create($item);
                }                
            }
        }
       
        return $this->getJsonOrDatatableResponse($request);        
    }

    public function update(InvenRequest $request, Inven $inven)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            return notPermissionResponse($request);
        }
        
        //$company->update($request->only(['name', 'email', 'code', 'url', 'logo', 'phone', 'location', 'status']));
        $inven->fill($request->all())->save();

        //if (requestAjaxOrJson($request))
            return new InvenResource($inven);
       
        //return redirect()->route($this->module.'.index')
        //        ->with('info', 'El registro '.$inven->descr.' ha sido modificado satisfactoriamente');
    }

    public function destroy(Inven $inven)
    {
        if ( ! hasPermission('delete-'.$this->permission) ) {
            return notPermissionResponse($request);
        }

        $name = $inven->descr;
        $inven->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro '.$name . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    public function show(Inven $inven)
    {
        if ( ! hasPermission($this->permission) ) {
            return notPermissionResponse($inven);
        }
        return  new InvenResource($inven);
    }

    public function create()
    {
        if ( ! hasPermission('create-'.$this->permission) ) {
            abort(403);
        }

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function edit(Inven $inven)
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['inven', 'route']));
    }

    public function showPrintList()
    {
        if ( ! hasPermission('edit-'.$this->permission) ) {
            abort(403);
        }
        $inven = new Inven();
        $prods = $inven->getData();
        $print = 1;
        
        $report_data = $inven->getReportConfig();

        return view($this->module.'.partials.print', compact(['prods', 'print', 'report_data']));
    }

    public function getProducts(Request $request)
    {
        return Response::json((new Inven)->getProducts($request));
    }

    public function getProductsSearch(Request $request)
    {
        return Response::json((new Inven)->searchProduct($request->term['term']));
    }

    
}
