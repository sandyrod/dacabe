<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandRequest extends FormRequest
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
        $command_id = isset( $_REQUEST['command_id'] ) ?  $_REQUEST['command_id'] : 0;
        
        return [
            'command' => 'required|string|min:3|max:100',
            'command_response' => 'required|string|min:3|max:100'
        ];
    }

     public function attributes(){
        return [
            'command'=>'Comando',
            'command_response'=>'Respuesta del Comando',
        ];
    }

   
}
