<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Http\Requests\VendedorRequest;
use App\Models\{Vendedor, Zona, Deposito};
use App\Traits\AdministradorTrait;

use Illuminate\Support\Facades\Response;

class AdministradoresController extends Controller
{
    use AdministradorTrait;

    private $permission;
    private $module;

    public function __construct()
    {
        $this->middleware('auth');
        $this->permission = 'vendedores';
        $this->module = 'vendedores';
    }

    public function index(Request $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
     
        $vendedores = (new Vendedor)->getAdminData();
        
        if (! requestAjaxOrJson($request, $vendedores)) {
            return view($this->module.'.index');
        }
        

        return $this->getJsonOrDatatableResponse($request, $vendedores);

    }

    public function store(VendedorRequest $request)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->createNew($request);
       
        return redirect()->route($this->permission.'.index')->with('info', 'El registro ha sido creado satisfactoriamente');
    }

    public function create()
    {
        if ( ! hasPermission($this->permission) ) {
            abort(403);
        }

        $zonas = (new Zona)->getData();
        $depositos = (new Deposito)->getData();

        $route = $this->permission.'.index';
        return view($this->module.'.create', compact(['route', 'zonas', 'depositos']));
    }

    public function update(VendedorRequest $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);
        
        $vendedor = (new Vendedor)->updateItem($code, $request);
       
        return redirect()->route($this->permission.'.index')
                ->with('info', 'El registro ha sido modificado satisfactoriamente');
    }

    public function edit(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->getData($code);
        $route = $this->permission.'.index';

        $zonas = (new Zona)->getData();
        $depositos = (new Deposito)->getData();

        return view($this->module.'.edit', compact(['vendedor', 'route', 'zonas', 'depositos']));
    }

    public function destroy(Request $request, $code)
    {
        if ( ! hasPermission($this->permission) )
            abort(403);

        $vendedor = (new Vendedor)->deleteRecord($code);

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El registro ha sido eliminado satisfactoriamente.'
        ], 200);
    }


    public function showPdfList()
    {
        $vendedores = (new Vendedor)->getData();

        $today = Carbon::now()->format('d/m/Y');

        $pdf = \PDF::loadView($this->module.'.partials.print', compact('vendedores', 'today'));
 
        return $pdf->download('lista_unidades.pdf');
    }

    public function showPrintList()
    {
        $vendedor = new Vendedor();
        $vendedores = $vendedor->getData();
        $print = 1;
        
        $report_data = $vendedor->getReportConfig();

        return view($this->module.'.partials.print', compact(['vendedores', 'print', 'report_data']));
    }


}
