<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleEditRequest extends FormRequest
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
        $role_id = isset( $_REQUEST['role_id'] ) ?  $_REQUEST['role_id'] : 0;
        
        return [
            'display_name' => 'required|string|min:4|max:100',
            'name' => 'required|string|min:4|max:30|unique:roles,name,'.$role_id,
        ];
    }

     public function attributes(){
        return [
            'display_name'=>'Nombre',
            'name'=>'Permiso',
        ];
    }

   
}
