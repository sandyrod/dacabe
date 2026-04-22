<?php

namespace App\Traits;

use App\Models\Serial;
use App\Models\Company;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\SerialCollection;

trait SerialTrait
{
    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $serials = new Serial();
            $serial = $serials->getData();
            return $this->getDatatableResponse($request, $serial);
        }
        
        return new SerialCollection(Serial::with('users')->with('company')->paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data){
        return DataTables::of($data)
            ->editColumn('user', function ( $data ) {
                return  $this->getUserColumn($data);
            })
            ->editColumn('company', function ( $data ) {
                return  $this->getCompanyColumn($data);
            })
            ->editColumn('created_at', function ( $data ) {
                return  $this->getCreatedAtColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })

            ->rawColumns(['created_at', 'user', 'company', 'action'])
            ->make(true);
    }

    public static function getUserColumn($data){
        return $data->user->name ?? 'No Definido';
    }

    public static function getCreatedAtColumn($data){
        return formatoFecha($data->created_at);
    }

    public static function getCompanyColumn($data){
        return $data->company->name ?? 'No Definido';
    }

    public static function getActionColumn($data){
        $buttons = [];
        /*
        if (hasPermission('edit-serial'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-serial'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        */
        return getActionHtmlColumn ($data, $buttons);
    }
}
