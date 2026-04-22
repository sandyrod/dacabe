<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use App\Models\ProductoFuturo;

trait ProductoFuturoTrait
{

    private function getJsonOrDatatableResponse(Request $request, $data)
    {
        if ($request->datatable) {
            return $this->getDatatableResponse($request, $data);
        } 
    }

    private function getDatatableResponse($request, $data)
    {
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->editColumn('descripcion', function ( $data ) {
                return  $this->getDescrColumn($data);
            })
            ->addColumn('caracteristicas', function ( $data ) {
                return  $this->getCaracteristicasColumn($data);
            })
            ->rawColumns(['action', 'descripcion', 'caracteristicas'])
            ->make(true);
    }

    public static function getDescrColumn($data)
    {
        return $data->descripcion;
        /*
        $html = $data->descripcion;
        if (hasOrderPermission()) {
            $url = 'order-inven/' . $data->CODIGO . '/edit';
            $html = '<a href="'.url($url).'">'.$data->descripcion.'</a>';
        }
        return $html; 
        */
    }

    public static function getCaracteristicasColumn($data)
    {
        return '<span class="badge badge-danger">PRONTO</span>';
        /*
        $html = '';
        $featured = (new Promocion)->where('codigo', $data->CODIGO)->first();
        if ($featured) {
            $html = $featured->nuevo ? '<span class="ml-2 badge badge-primary">NUEVO</span>' : '';
            $html .= $featured->promocion ? '<span class="ml-2 badge badge-warning">PROMOCION</span>' : '';
        }
        return $html; 
        */
    }

    public static function getActionColumn($data)
    {
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

        return getOrderActionHtmlColumn ($data, $buttons, 'codigo');
    }
    
}
