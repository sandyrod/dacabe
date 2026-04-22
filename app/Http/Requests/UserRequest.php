<?php

namespace App\Http\Requests;

//use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        isset( $_REQUEST['user_id'] ) ? $id = $_REQUEST['user_id'] : $id = 0;
        $pass='required|min:6|confirmed'; 
        $cc='required|min:6';
        
        if ($id>0){ 
            $pass='nullable|min:6|confirmed'; 
            $cc=''; 
        }

        return [
            //'document' => 'required|min:5|max:10|unique:users,document,'.$id,
            'name' => 'required|string|max:100',
            //'email' => 'required|email|max:100|unique:users,email,'.$id,
            'password' => $pass,
            'password_confirmation' => $cc,
        ];
    }

    public function attributes(){
        return [
            'document' => 'cédula',
            'name'=>'Usuario',
            'email'=>'correo',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmar contraseña',
            'photo'=>'Imagen',
        ];
    }
}
