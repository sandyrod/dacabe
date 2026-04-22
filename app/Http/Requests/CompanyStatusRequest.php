<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyStatusRequest extends FormRequest
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
        $company_id = isset( $this->company_status->id ) ?  $this->company_status->id : 0;
        
        return [
            'name' => 'required|string|min:3|max:100|unique:company_status,name,'.$company_id
        ];
    }

     public function attributes(){
        return [
            'name'=>'Nombre'
        ];
    }

   
}
