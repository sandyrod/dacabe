<?php

namespace App\Traits;

use App\Models\Ftp;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\FtpCollection;

trait FtpTrait
{

    private function getJsonOrDatatableResponse(Request $request, $ftp)
    {      
        if ($request->datatable)
            return $this->getDatatableResponse($request, $ftp);
        
        return new FtpCollection(Ftp::paginate($this->paginate));
    }

    private function getDatatableResponse($request, $data)
    {
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->addColumn('drugstore_name', function ( $data ) {
                return  $this->getDrugstoreNameColumn($data);
            })
            ->addColumn('company_name', function ( $data ) {
                return  $this->getCompanyNameColumn($data);
            })
            ->rawColumns(['company_name', 'drugstore_name', 'action'])
            ->make(true);
    }

    public static function getDrugstoreNameColumn($data)
    {
        return ($data->drugstore) ? $data->drugstore->name : '';
    }

    public static function getCompanyNameColumn($data)
    {
        return ($data->company) ? $data->company->name : '';
    }

    public static function getActionColumn($data)
    {
        $buttons = [];
        if (hasPermission('edit-ftp'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-ftp'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn ($data, $buttons);
    }

    private function isMySession($user_id)
    {
        return $user_id == Auth::user()->id;
    }
    
}
