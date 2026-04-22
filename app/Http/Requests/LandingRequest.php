<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandingRequest extends FormRequest
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
        $landing_id = isset( $_REQUEST['landing_id'] ) ?  $_REQUEST['landing_id'] : 0;
        
        return [
            'theme_id' => 'required|integer'
        ];
    }

     public function attributes(){
        return [
            'theme_id'=>'Tema'
        ];
    }

   
}
