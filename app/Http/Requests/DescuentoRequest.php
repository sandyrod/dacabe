<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DescuentoRequest extends FormRequest
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
        $descuento_id = isset( $_REQUEST['descuento_id'] ) ?  $_REQUEST['descuento_id'] : 0;
        
        return [
            'nombre' => 'required|string|min:2|max:100',
            'porcentaje' => 'required|numeric|min:0|max:100',
        ];
    }

     public function attributes()
     {
        return [
            'nombre'=>'Nombre',
            'porcentaje'=>'Porcentaje'
        ];
    }

   
}
