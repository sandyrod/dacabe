<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FtpInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $ftp_invoice_id = isset( $this->ftp_invoice->id ) ?  $this->ftp_invoice->id : 0;
        
        return [
            'company_id' => 'required|number',
            'number' => 'required|string|min:3|max:50|unique:ftp_invoices,number,'.$ftp_invoice_id
        ];
    }

     public function attributes(){
        return [
            'company_id' => 'Empresa',
            'number' => 'Nro de Factura',
        ];
    }

   
}
