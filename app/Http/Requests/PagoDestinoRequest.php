<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagoDestinoRequest extends FormRequest
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
        $pago_destino_id = isset( $_REQUEST['pago_destino_id'] ) ?  $_REQUEST['pago_destino_id'] : 0;
        
        return [
            'nombre' => 'required|string|min:2'
        ];
    }

     public function attributes()
     {
        return [
            'nombre'=>'Nombre',
        ];
    }

   
}
