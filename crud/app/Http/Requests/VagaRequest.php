<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VagaRequest extends FormRequest
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
            'titulo' => 'required|string|max:255|min:5',
            'descricao' => 'required|string',
            'salario' => 'nullable|numeric|min:0',
            'ativa' => 'nullable|boolean'
        ];
    }

    public function messages(): array{
        return[
            'titulo.required' => 'Titulo Obrigatório',
            'titulo.min' => 'Titulo muito curto, deve ser acima de 5 caracteres',
            'titulo.max' => 'Titulo muito extenso, deve ser no maximo 255 caracteres',
            'descricao.required' => 'Descrição Obrigatória',
            'salario.min' => 'O valor do salario deve ser maior que 0',

        ];
    }
}
