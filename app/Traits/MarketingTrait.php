<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Marketing;

trait MarketingTrait
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
            ->editColumn('tipo', function ( $data ) {
                return  $this->getTipoColumn($data);
            })
            ->editColumn('statistics', function ( $data ) {
                return  $this->getStatisticsColumn($data);
            })
            ->editColumn('estatus', function ( $data ) {
                return  $this->getEstatusColumn($data);
            })
            ->editColumn('created_at', function ( $data ) {
                return  $this->getDateAtColumn($data);
            })
            ->rawColumns(['action', 'descripcion', 'tipo', 'estatus', 'statistics', 'created_at'])
            ->make(true);
    }

    private static function getDateAtColumn($data)
    {
        return formatoFechaDMA($data->created_at);        
    }

    private static function getStatisticsColumn($data)
    {
        $i = $data->marketing_detalle->where('estatus', 'Procesado')->count();
        $n = $data->marketing_detalle->count();
        $icon = $data->tipo=='whatsapp' ? 'phone' : 'envelope';
        $html = '<span><i class="fa fa-'.$icon.'"></i> '.$i.'/'.$n.'</span>';
        
        return $html;
    }

    private static function getEstatusColumn($data)
    {
        $style = $data->estatus == 'Procesado' ? 'success' : 'warning';
        $html = '<span class="badge badge-'.$style.'">'.$data->estatus.'</span>';
        
        return $html;
    }

    public static function getTipoColumn($data)
    {
        if (substr($data->tipo, 0, 8)=='producto'){
            return strtoupper(substr($data->tipo, 9, 1)) . substr($data->tipo, 10);
        }
        return strtoupper(substr($data->tipo, 0, 1)) . substr($data->tipo, 1);
    }

    public static function getDescrColumn($data)
    {
        return '<b>' . $data->descripcion . '</b><br /><small>' . $data->codigo . '</small>';
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
            if ($data->estatus == 'pendiente') {
                $buttons[] = [
                    'style' => 'primary btn-sm',
                    'name' => 'edit',
                    'hint' => 'Editar',
                    'icon' => 'edit',
                ];
            }

            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];

            if ($data->estatus == 'pendiente') {
                $buttons[] = [
                    'style' => 'success btn-sm',
                    'name' => 'marketing',
                    'hint' => 'Procesar',
                    'icon' => 'check',
                ];                
            }

            $buttons[] = [
                'style' => 'warning btn-sm',
                'name' => 'view',
                'hint' => 'Ver detalles',
                'icon' => 'eye',
            ];
        }        
        return getActionHtmlColumn ($data, $buttons);
    }
    
}
