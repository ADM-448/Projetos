<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EmpresaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome'=> 'required|string|min:3|max:255',
            'cidade' => 'required|string',
            'estado' => 'required|string',
            'email'=>'required|string',
            'site'=>'string',

        ];
    }

    public function messages(): array{
        return[
            'nome.required'=>'Nome Obrigatório',
            'cidade.required'=>'Cidade Obrigatório',
            'estado.required'=>'Estado Obrigatório',
            'email.required'=>'Email Obrigatório',
        ];
    }
}
