<?php

namespace App\Traits;

use App\Permission;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use DB;

trait PermissionTrait
{

    private function getJsonOrDatatableResponse(Request $request, $permissions){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $permissions);
        
        //return $this->getJsonResponse($request, $permissions);
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasPermission('edit-permission'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-permission'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];

        return getActionHtmlColumn ($data, $buttons);
    }
}
