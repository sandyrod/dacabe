<?php

namespace App\Traits;

use App\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

trait CategoryTrait
{

    private function getJsonOrDatatableResponse(Request $request, $categories){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $categories);
        
        //return $this->getJsonResponse($request, $categories);
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->editColumn('icon', function ( $data ) {
                return  $this->getIconColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['icon', 'action'])
            ->make(true);
    }

    public static function getIconColumn($data){
        if ($data->icon)
            return '<span><i class="'.$data->icon.'"></i></span>';
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasPermission('edit-category'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-category'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn ($data, $buttons);
    }

    private function isMySession($user_id){
        return $user_id == Auth::user()->id;
    }
    
}
