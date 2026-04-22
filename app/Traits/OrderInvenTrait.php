<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Promocion;

trait OrderInvenTrait
{

    private function getJsonOrDatatableResponse(Request $request, $data)
    {
        if ($request->datatable) {
            return $this->getDatatableResponse($request, $data);
        }        
        //return $this->getJsonResponse($request, $data);
    }

    private function getDatatableResponse($request, $data)
    {
        return DataTables::of($data)
            ->addColumn('action', function ( $data ) {
                return  $this->getActionColumn($data);
            })
            ->editColumn('DESCR', function ( $data ) {
                return  $this->getDescrColumn($data);
            })
            ->addColumn('caracteristicas', function ( $data ) {
                return  $this->getCaracteristicasColumn($data);
            })
            ->rawColumns(['action', 'DESCR', 'caracteristicas'])
            ->make(true);
    }

    public static function getDescrColumn($data)
    {
        $html = $data->DESCR;
        if (hasOrderPermission()) {
            $url = 'order-inven/' . $data->CODIGO . '/edit';
            $html = '<a href="'.url($url).'">'.$data->DESCR.'</a>';
        }
        return $html;
    }

    public static function getCaracteristicasColumn($data)
    {
        $html = '';
        $featured = (new Promocion)->where('codigo', $data->CODIGO)->first();
        if ($featured) {
            $html = $featured->nuevo ? '<span class="ml-2 badge badge-primary">NUEVO</span>' : '';
            $html .= $featured->promocion ? '<span class="ml-2 badge badge-warning">PROMOCION</span>' : '';
        }
        if (@$data->informacion){
            if (@$data->informacion->detalle && $data->informacion->detalle != ''){
                $html .= '<span class="ml-2 hint--top" aria-label="'.$data->informacion->detalle.'"><i class="fa fa-eye"></i></span>';
            }
            if (@$data->informacion->observaciones && $data->informacion->observaciones != ''){
                $html .= '<span class="ml-2 hint--top" aria-label="'.$data->informacion->observaciones.'"><i class="fa fa-edit"></i></span>';
            }
            if (@$data->informacion->descuento){
                $html .= '<span class="ml-2 badge badge-danger hint--top" aria-label="Descuento: '.$data->informacion->descuento.'"><i class="fa fa-percent"></i></span>';
            }
            if (@$data->informacion->comision){
                $html .= '<span class="ml-2 badge badge-primary hint--top" aria-label="Comisión: '.$data->informacion->comision.'"><i class="fa fa-percent"></i></span>';
            }
        }
        return $html;
    }

    public static function getActionColumn($data)
    {
        $buttons = [];
        if (hasOrderPermission()) {
            /*
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
            */
            $buttons[] = [
                'style' => 'outline-primary btn-sm',
                'name' => 'photos',
                'hint' => 'Fotos',
                'icon' => 'camera',
            ];

        }

        return getOrderActionHtmlColumn ($data, $buttons, 'CODIGO');
    }
    
}
