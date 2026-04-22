<?php

namespace App\Traits;

use App\Models\Command;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\CommandCollection;

trait CommandTrait
{

    private function getJsonOrDatatableResponse(Request $request, $commands){      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $commands);
        
        return new CommandCollection(Command::paginate($this->paginate));
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
        if (hasPermission('edit-command'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-command'))
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
