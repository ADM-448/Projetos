<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompetenciaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $empresaId = $this->user()->empresa_id;
        $competenciaId = $this->route('competencia')->id;

        return [
            'nome' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('competencias')->where('empresa_id', $empresaId)->ignore($competenciaId)
            ],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
