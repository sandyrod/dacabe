<?php

namespace App\Traits;

use App\Models\Theme;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\ThemeCollection;

trait ThemeTrait
{

    private function getJsonOrDatatableResponse(Request $request, $themes){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $themes);
        
        return new ThemeCollection(Theme::paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['icon', 'action'])
            ->make(true);
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasPermission('edit-themes'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-themes'))
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
