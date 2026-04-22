<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use Illuminate\Support\Facades\Response;
use DataTables;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    private $permission = 'bank'; // Asignar permiso si existe, si no, validar uso.
    private $module = 'banks';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // if ( ! hasPermission($this->permission) ) abort(403);
     
        $banks = Bank::all();
     
        return (requestAjaxOrJson($request, $banks)) ? $this->getJsonOrDatatableResponse($request, $banks) : view($this->module.'.index');
    }

    public function create()
    {
        // if ( ! hasPermission('create-'.$this->permission) ) abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.create', compact(['route']));
    }

    public function store(Request $request)
    {
        // if ( ! hasPermission('create-'.$this->permission) ) abort(403);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:20',
        ]);

        $bank = Bank::create($request->all());
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El banco '.$bank->nombre.' ha sido creado satisfactoriamente');
    }

    public function edit(Bank $bank)
    {
        // if ( ! hasPermission('edit-'.$this->permission) ) abort(403);

        $route = $this->module.'.index';
        return view($this->module.'.edit', compact(['bank', 'route']));
    }

    public function update(Request $request, Bank $bank)
    {
        // if ( ! hasPermission('edit-'.$this->permission) ) abort(403);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:20',
        ]);

        $bank->fill($request->all())->save();
       
        return redirect()->route($this->module.'.index')
                ->with('info', 'El banco '.$bank->nombre.' ha sido modificado satisfactoriamente');
    }

    public function destroy(Bank $bank)
    {
        // if ( ! hasPermission('delete-'.$this->permission) ) abort(403);

        $nombre = $bank->nombre;
        $bank->delete();

        return Response::json([
            'type' => 'success',
            'title' => 'Registro Eliminado!',
            'text' => 'El banco '.$nombre . ' ha sido eliminado satisfactoriamente.'
        ], 200);
    }

    // Identical helpers to CategoryTrait but inlined
    private function getJsonOrDatatableResponse(Request $request, $data){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $data);
        
        //return $this->getJsonResponse($request, $data);
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getActionColumn($data){
        $buttons = [];
        // if (hasPermission('edit-'.$this->permission))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        // if (hasPermission('delete-'.$this->permission))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn($data, $buttons);
    }
}
