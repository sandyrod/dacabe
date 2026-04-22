<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketingRequest extends FormRequest
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
        $marketing_id = isset( $_REQUEST['marketing_id'] ) ?  $_REQUEST['marketing_id'] : 0;
        
        return [
            'codigo' => 'required|string|min:2|max:20',
            'tipo' => 'required|string|min:3|max:100',
        ];
    }

     public function attributes()
     {
        return [
            'codigo' => 'Código',
            'tipo' => 'Tipos'
        ];
    }

   
}
