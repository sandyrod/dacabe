<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SerialRequest extends FormRequest
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
        $company_id = isset( $this->company->id ) ?  $this->company->id : 0;
        
        return [
            'company_id' => 'required|integer'
        ];
    }

     public function attributes(){
        return [
            'company_id'=>'Empresa'
        ];
    }

   
}
