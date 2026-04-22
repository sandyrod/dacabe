<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

trait TipprodTrait
{

    private function getJsonOrDatatableResponse(Request $request, $data)
    {
        if ($request->datatable) {
            return $this->getDatatableResponse($request, $data);
        }        
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

        return getOrderActionHtmlColumn ($data, $buttons, 'CTIPPROD');
    }
    
}
