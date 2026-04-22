<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class InvenRequest extends FormRequest
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
        return [
            'descr' => 'required|string|min:3|max:150',
            'tipo' => 'required|string|min:1|max:100',
            'unidademp' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'descr.required' => 'Descripcion es obligatorio',
            'tipo.required' => 'Tipo es obligatorio',
            'unidademp.required' => 'Unidad Empaque es obligatorio',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }

   
}
