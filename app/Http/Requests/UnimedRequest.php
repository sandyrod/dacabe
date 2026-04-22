<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnimedRequest extends FormRequest
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
        $unimed_id = isset( $_REQUEST['unimed_id'] ) ?  $_REQUEST['unimed_id'] : 0;
        
        return [
            'CUNIMED' => 'required|string|min:2|max:6',
            'DUNIMED' => 'required|string|min:3|max:60',
        ];
    }

     public function attributes()
     {
        return [
            'CUNIMED'=>'Código Unidad',
            'DUNIMED'=>'Descripción'
        ];
    }

   
}
