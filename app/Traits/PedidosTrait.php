<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Models\{Pedido, PedidoFactura};

trait PedidosTrait
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
            ->rawColumns(['action', 'total', 'status', 'fecha', 'cliente', 'nro', 'warehouse', 'observations', 'conditions'])
            ->make(true);
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
        $html = '';
        $factura = (new PedidoFactura)->where('pedido_id', $data->id)->first();
        if ($factura){
            $html = '<br /><span class="badge badge-success badge-pill">Fact: '.$factura->factura.'</span>';
        }
        if ($data->factura=='SI'){
            $html .= '<br /><span class="badge badge-primary badge-pill">Con Factura</span>';
        } else{
            $html .= '<br /><span class="badge badge-secondary badge-pill">Sin Factura</span>';
        }
        return str_pad($data->id, 5, '0', STR_PAD_LEFT).$html;
    }

    public static function getFechaColumn($data)
    {
        $color = $data->cliente_verificado ? 'success' : 'danger';
        $image = ($data->rif_foto) ? asset('storage/products/') . '/' . $data['rif_foto'] : '';
        $rif = $data->descripcion ? '<a class="cliente_verificado btn btn-sm btn-outline-'.$color.' mr-2" href="#" data-iddata="'.$data->id.'" ><i class="fa fa-check"></i></a>' : '';
        $rif .= ($data->rif_foto) ? '<a class="btn btn-outline-danger btn-sm" href="'.$image.'" target="_blank"><i class="fa fa-camera"></i></a>' : '';
        $html = formatoFechaDMASimple($data->fecha) . '<br>' . $rif;
        
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
        if (hasOrderPermission()) {
            /*
            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];
            */

            if ($data->estatus == 'PENDIENTE') {
                $buttons[] = [
                    'style' => 'success btn-sm mt-1',
                    'name' => 'approve',
                    'hint' => 'Aprobar',
                    'icon' => 'check',
                ];
                $buttons[] = [
                    'style' => 'danger btn-sm mt-1',
                    'name' => 'delete',
                    'hint' => 'Rechazar',
                    'icon' => 'times',
                ];
            }


            //if ($data->estatus == 'APROBADO') {
                $buttons[] = [
                    'style' => 'outline-primary btn-sm mt-1',
                    'name' => 'email',
                    'hint' => 'Email',
                    'icon' => 'envelope',
                ];
            //}
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

            $buttons[] = [
                'style' => 'outline-danger btn-sm mt-1',
                'name' => 'pdf',
                'hint' => 'Pdf',
                'icon' => 'file-pdf',
            ];

        }

        return getActionHtmlColumn ($data, $buttons);
    }
    
}
