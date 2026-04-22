<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

trait AdministradorTrait
{

    private function getJsonOrDatatableResponse(Request $request, $data)
    {
        if ($request->datatable) {
            return $this->getDatatableResponse($request, $data);
        } 
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->editColumn('name', function ( $data ) {
                return  $this->getNameColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->rawColumns(['action', 'name', 'zona', 'deposito'])
            ->make(true);
    }

    private function getNameColumn($data)
    {
        if (@$data->user) {
            return $data->user->name . ' ' . $data->user->last_name;
        }
    }

    
    public static function getActionColumn($data){
        $buttons = [];
        if (hasOrderPermission()) {
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        }

        return getOrderActionHtmlColumn ($data, $buttons, 'id');
    }
    
}
