<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FtpRequest extends FormRequest
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
        $ftp_id = isset( $_REQUEST['ftp_id'] ) ?  $_REQUEST['ftp_id'] : 0;
        
        return [
            'drugstore_id' => 'required|integer',
            'remote_dir' => 'required|string|min:2|max:100',
            'local_dir' => 'required|string|min:2|max:100'
        ];
    }

     public function attributes(){
        return [
            'drugstore_id' => 'Drogueria',
            'remote_dir' => 'Directorio Remoto',
            'local_dir' => 'Directorio Local'
        ];
    }

   
}
