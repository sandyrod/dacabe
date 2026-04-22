<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'code' => 'required|string|min:6|max:20',
            'name' => 'required|string|min:3|max:100|unique:companies,name,'.$company_id
        ];
    }

     public function attributes(){
        return [
            'code'=>'RIF',
            'name'=>'Nombre',
        ];
    }

   
}
