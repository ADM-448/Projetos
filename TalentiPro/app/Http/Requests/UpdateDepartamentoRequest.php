<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para atualização de departamento.
 */
class UpdateDepartamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Regras de validação com isolamento de tenant.
     * Rule::exists()->where('empresa_id', ...) impede que o gestor_id
     * de outra empresa seja vinculado (Cross-Tenant Data Leak).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'nome'      => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'gestor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('empresa_id', $empresaId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required'    => 'O nome do departamento é obrigatório.',
            'gestor_id.exists' => 'O gestor selecionado não pertence a esta empresa.',
        ];
    }
}
