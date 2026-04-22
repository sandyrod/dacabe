<?php

namespace App\Traits;

use App\Models\FtpInvoice;
use App\Models\FtpInvoiceDetail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;

use App\Http\Resources\FtpInvoiceCollection;

trait FtpInvoiceTrait
{

    private function getJsonOrDatatableResponse(Request $request){      
        if ($request->datatable){
            $ftp_invoices = new FtpInvoice();
            $ftp_invoice = $ftp_invoices->getData();
            return $this->getDatatableResponse($request, $ftp_invoice);
        }
        
        return new FtpInvoiceCollection(FtpInvoice::with('ftp_invoice_details')->with('companies')->paginate($this->paginate));
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
        $html = '';
        if (hasPermission('edit-ftp-invoice')){
            $html = '<a href="#" data-iddata="'.$data->id.'" data-namedata="'.$data->name.'" class="btn btn-warning btn-sm permission hint--top" aria-label="Módulos Disponibles"><i class="fa fa-cogs"></i></a> ';

            $buttons[] = [
                'style' => 'primary btn-sm',
                'name' => 'edit',
                'hint' => 'Editar',
                'icon' => 'edit',
            ];
        }

        if (hasPermission('delete-ftp-invoice'))
            $buttons[] = [
                'style' => 'danger btn-sm',
                'name' => 'delete',
                'hint' => 'Eliminar',
                'icon' => 'trash',
            ];
        
        $html .= getActionHtmlColumn ($data, $buttons);

        return $html;
    }
    
}
