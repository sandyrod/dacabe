<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\{Deposito};
use DataTables;

trait VendedorTrait
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
            ->editColumn('estatus', function ( $data ) {
                return  $this->getEstatusColumn($data);
            })
            ->editColumn('zona', function ( $data ) {
                return  $this->getZonaColumn($data);
            })
            ->editColumn('deposito', function ( $data ) {
                return  $this->getDepositoColumn($data);
            })
            ->editColumn('document', function ( $data ) {
                return  $this->getDocumentColumn($data);
            })
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->rawColumns(['action', 'name', 'estatus', 'zona', 'deposito', 'document'])
            ->make(true);
    }

    private function getNameColumn($data)
    {
        if (@$data->user) {
            return $data->user->name . ' ' . $data->user->last_name;
        }
    }

    private function getDocumentColumn($data)
    {
        if (@$data->user) {
            return $data->user->document;
        }
    }

    private function getZonaColumn($data)
    {
        if (@$data->zona) {
            return $data->zona->nombre;
        }
    }

    private function getEstatusColumn($data)
    {
        $estatus = strtoupper(trim((string) @$data->estatus));
        if ($estatus === 'SUSPENDIDO') {
            return '<span class="badge badge-danger">Suspendido</span>';
        }

        return '<span class="badge badge-success">Activo</span>';
    }

    private function getDepositoColumn($data)
    {
        $html = '';
        if (@$data->depositos) {
            foreach($data->depositos as $deposito) {
                $dep = (new Deposito)->where('CDEPOS', $deposito->CDEPOS)->first();
                if ($dep) {
                    $html .= $dep->DDEPOS . ', ';
                }
            }
            return substr($html, 0, strlen($html)-2);
        }
        return $html;
    }

    public static function getActionColumn($data){
        $buttons = [];
        if (hasOrderPermission()) {
            $suspendido = strtoupper(trim((string) @$data->estatus)) === 'SUSPENDIDO';

            $buttons[] = [
                'style' => $suspendido ? 'success btn-sm' : 'warning btn-sm',
                'name' => 'toggle-status',
                'hint' => $suspendido ? 'Habilitar' : 'Suspender',
                'icon' => $suspendido ? 'play' : 'pause',
            ];

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
