<?php

namespace App\Traits;

use App\Models\CompanyStatus;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\CompanyStatusCollection;

trait CompanyStatusTrait
{

    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $status = new CompanyStatus();
            $company_status = $status->getData();
            return $this->getDatatableResponse($request, $company_status);
        }
        
        return new CompanyStatusCollection(CompanyStatus::with('companies')->paginate($this->paginate));
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
        if (hasPermission('edit-company-status'))
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];

        if (hasPermission('delete-company-status'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        return getActionHtmlColumn ($data, $buttons);
    }
        
}
