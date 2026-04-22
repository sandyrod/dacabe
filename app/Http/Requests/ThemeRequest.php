<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemeRequest extends FormRequest
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
        $theme_id = isset( $_REQUEST['theme_id'] ) ?  $_REQUEST['theme_id'] : 0;
        
        return [
            'name' => 'required|string|min:3|max:100'
        ];
    }

     public function attributes(){
        return [
            'name'=>'Nombre'
        ];
    }

   
}
