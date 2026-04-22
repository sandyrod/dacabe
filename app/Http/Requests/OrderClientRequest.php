<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderClientRequest extends FormRequest
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
        $order_client_id = isset( $_REQUEST['order_client_id'] ) ?  $_REQUEST['order_client_id'] : 0;
        
        return [
            'name' => 'required|string|min:2|max:100'
        ];
    }

     public function attributes()
     {
        return [
            'name'=>'Nombre'
        ];
    }

   
}
