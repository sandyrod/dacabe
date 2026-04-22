<?php

namespace App\Traits;

use App\Models\Notification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\NotificationCollection;

trait NotificationTrait
{

    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $notification = (new Notification)->getData();
            return $this->getDatatableResponse($request, $notification);
        }
        
        return new NotificationCollection(Notification::paginate($this->paginate));
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
        $html = '';
        if (hasPermission('edit-notification')){
            $html = '<a href="#" data-iddata="'.$data->id.'" data-namedata="'.$data->name.'" class="btn btn-warning btn-sm permission hint--top" aria-label="Módulos Disponibles"><i class="fa fa-cogs"></i></a> ';

            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];
        }

        if (hasPermission('delete-notification'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        
        $html .= getActionHtmlColumn ($data, $buttons);

        return $html;
    }

    private function isMySession($user_id){
        return $user_id == Auth::user()->id;
    }
    
}
