<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipprodRequest extends FormRequest
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
        $tipprod_id = isset( $_REQUEST['tipprod_id'] ) ?  $_REQUEST['tipprod_id'] : 0;
        
        return [
            'CTIPPROD' => 'required|string|min:2|max:6',
            'DTIPPROD' => 'required|string|min:3|max:30',
        ];
    }

     public function attributes()
     {
        return [
            'CTIPPROD'=>'Código',
            'DTIPPROD'=>'Descripción'
        ];
    }

   
}
