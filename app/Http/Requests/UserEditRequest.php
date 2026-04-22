<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
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
        $user_id = isset( $_REQUEST['user_id'] ) ?  $_REQUEST['user_id'] : 0;
        
        return [
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100|unique:users,email,'.$user_id,
            'password' => 'nullable|min:6|confirmed',
        ];
    }

   
}
