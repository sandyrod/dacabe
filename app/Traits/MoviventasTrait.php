<?php

namespace App\Traits;

use App\Models\Moviventa;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\InvenCollection;

trait MoviventasTrait
{

    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $inven = (new Moviventa)->getData();
            return $this->getDatatableResponse($request, $moviventas);
        }
        
        return new MoviventasCollection(Moviventa::where('company_id', Auth::user()->company_id)->paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
        /*
            ->editColumn('status', function ( $data ) {
                return  $this->getStatusColumn($data);
            })
            */
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public static function getStatusColumn($data){
        $descr = $data->company_status->name ?? 'No Definido';
        $style = $descr=='Activo' ? 'success' : ($descr=='Suspendido'?'warning':'danger');

        return '<a href="#" data-iddata="'.$data->id.'" class="badge badge-'.$style.' change hint--top" aria-label="Cambiar Estatus">'.$descr.'</a>';
    }

    public static function getActionColumn($data){
        $buttons = [];
        $html = '';
        if (hasPermission('edit-inven')){
            $html = '<a href="#" data-iddata="'.$data->id.'" data-namedata="'.$data->name.'" class="btn btn-warning btn-sm permission hint--top" aria-label="Módulos Disponibles"><i class="fa fa-cogs"></i></a> ';

            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];
        }

        if (hasPermission('delete-inven'))
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
