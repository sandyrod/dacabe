<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DptoRequest extends FormRequest
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
        $dpto_id = isset( $_REQUEST['dpto_id'] ) ?  $_REQUEST['dpto_id'] : 0;
        
        return [
            'CDPTO' => 'required|string|min:2|max:6',
            'DDPTO' => 'required|string|min:3|max:30',
        ];
    }

     public function attributes()
     {
        return [
            'CDPTO'=>'Código',
            'DDPTO'=>'Descripción'
        ];
    }

   
}
