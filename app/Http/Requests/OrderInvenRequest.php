<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderInvenRequest extends FormRequest
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
        $inven_id = isset( $_REQUEST['inven_id'] ) ?  $_REQUEST['inven_id'] : 0;
        
        return [
            //'CODIGO' => 'required|string|min:2|max:6',
            //'descripcion' => 'required|string|min:3|max:100',
        ];
    }

     public function attributes()
     {
        return [
            //'CODIGO'=>'Código',
            //'descripcion'=>'Descripción'
        ];
    }

   
}
