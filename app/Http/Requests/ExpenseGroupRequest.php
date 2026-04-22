<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseGroupRequest extends FormRequest
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
        $expense_group_id = isset( $_REQUEST['expense_group_id'] ) ?  $_REQUEST['expense_group_id'] : 0;
        
        return [
            'name' => 'required|string|min:3',
        ];
    }

     public function attributes()
     {
        return [
            'name'=>'Nombre'
        ];
    }

   
}
