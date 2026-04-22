<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $category_id = isset( $_REQUEST['category_id'] ) ?  $_REQUEST['category_id'] : 0;
        
        return [
            'description' => 'required|string|min:6|max:200'
        ];
    }

     public function attributes(){
        return [
            'description'=>'Categoria'
        ];
    }

   
}
