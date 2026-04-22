<?php

namespace App\Traits;

use App\Models\Module;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\ModuleCollection;

trait ModuleTrait
{

    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $module = new Module();
            $modules = $module->getData();
            return $this->getDatatableResponse($request, $modules);
        }
        
        return new ModuleCollection(Module::with('user')->paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->editColumn('status', function ( $data ) {
                return  $this->getStatusColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public static function getStatusColumn($data){
        $element = getModuleStatus($data->status);
        return '<span class="badge badge-'.$element[1].'">'.$element[0].'</span>';
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasPermission('edit-module'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-module'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn ($data, $buttons);
    }    
}
