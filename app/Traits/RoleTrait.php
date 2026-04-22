<?php

namespace App\Traits;

use App\Models\Role;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use DB;

trait RoleTrait
{

    private function getJsonOrDatatableResponse(Request $request, $roles){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $roles);
        
        //return $this->getJsonResponse($request, $roles);
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->editColumn('permissions', function ( $data ) {
                return $this->getPermissionsColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['permissions', 'action'])
            ->make(true);
    }

    public static function getPermissionsColumn($data){
        return DB::table('permission_role')->where('role_id', $data->id)->count();
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasPermission('edit-role'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-role'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];

        return getActionHtmlColumn ($data, $buttons);
    }
}
