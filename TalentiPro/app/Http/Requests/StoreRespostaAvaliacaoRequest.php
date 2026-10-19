<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRespostaAvaliacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A autorização deve ser feita na policy, checando se o usuario logado é o avaliador_id do participante
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
            'respostas' => ['required', 'array'],
            'respostas.*' => ['required', 'integer', 'min:0', 'max:5'], // Chave = skill_id, Valor = nota (0 a 5)
            'evidencias' => ['nullable', 'array'],
            'evidencias.*' => ['nullable', 'string', 'max:3000'], // Chave = skill_id, Valor = texto de evidencia
            'observacao' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
