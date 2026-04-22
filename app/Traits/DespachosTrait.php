<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Pedido;

trait DespachosTrait
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
            ->editColumn('total', function ( $data ) {
                return  $this->getTotalColumn($data);
            })
            ->editColumn('fecha', function ( $data ) {
                return  $this->getFechaColumn($data);
            })
            ->editColumn('cliente', function ( $data ) {
                return  $this->getClienteColumn($data);
            })
            ->editColumn('nro', function ( $data ) {
                return  $this->getNroColumn($data);
            })
            ->editColumn('status', function ( $data ) {
                return  $this->getEstatusColumn($data);
            })
            ->addColumn('warehouse', function ( $data ) {
                return  $this->getWarehouseColumn($data);
            })
            ->addColumn('observations', function ( $data ) {
                return  $this->getObservationsColumn($data);
            })
            ->addColumn('conditions', function ( $data ) {
                return  $this->getConditionsColumn($data);
            })
            ->addColumn('fecha_despacho', function ( $data ) {
                return $this->getFechaDespachoColumn($data);
            })
            ->rawColumns(['action', 'total', 'status', 'fecha', 'cliente', 'nro', 'warehouse', 'observations', 'conditions', 'fecha_despacho'])
            ->make(true);
    }

    public static function getFechaDespachoColumn($data)
    {
        if (!$data->fecha_despacho) {
            return '';
        }
        $fecha = \Carbon\Carbon::parse($data->fecha_despacho)->format('d/m/Y');
        return '<span class="badge badge-success">' . $fecha . '</span>';
    }


    public static function getEstatusColumn($data)
    {
        $style = getOrderStatusColor($data->estatus);
        return '<span class="badge badge-'.$style.'">'.$data->estatus.'</span>';        
    }

    public static function getObservationsColumn($data)
    {
        return $data->observations;        
    }

    public static function getConditionsColumn($data)
    {
        return $data->conditions;        
    }

    public static function getWarehouseColumn($data)
    {
        $html = '<b>' . $data->seller_code .'</b><br />';
        $html .= $data->deposito ? $data->deposito->DDEPOS : '';        
        return $html;        
    }

    public static function getNroColumn($data)
    {
        $nro = str_pad($data->id, 5, '0', STR_PAD_LEFT);
        
        // Agregar número de factura si existe
        $facturaHtml = '';
        if (isset($data->numero_factura) && $data->numero_factura) {
            $facturaHtml = '<br><span class="badge badge-info badge-pill">' . $data->numero_factura . '</span>';
        }
        
        return $nro . $facturaHtml;
    }

    public static function getFechaColumn($data)
    {
        $html = formatoFechaDMASimple($data->fecha);
        
        return $html;
    }

    public static function getClienteColumn($data)
    {
        $color = $data->cliente_verificado ? 'success' : 'danger';
        $html = '<strong class="text-'.$color.'">' . $data->descripcion . '</strong><br>';
        $html .= $data->rif ? '<small>RIF: ' . $data->rif . '</small>' : '';
        return $html;
    }

    public static function getTotalColumn($data)
    {
        $total = '$ ' . 0;

        return $total;
    }

    public static function getActionColumn($data)
    {
        $buttons = [];
        if ( hasPermission('despachos') ) {
            $buttons[] = [
                'style' => 'success btn-sm mt-1',
                'name' => 'despachar',
                'hint' => 'Despachar Pedido',
                'icon' => 'truck',
            ];
        }

        if (hasOrderClientPermission()){
            $buttons[] = [
                'style' => 'info btn-sm mt-1',
                'name' => 'view',
                'hint' => 'Ver Pedido',
                'icon' => 'eye',
            ];

            $buttons[] = [
                'style' => 'warning btn-sm mt-1',
                'name' => 'print',
                'hint' => 'Imprimir',
                'icon' => 'print',
            ];

        }

        return getActionHtmlColumn ($data, $buttons);
    }
    
}
