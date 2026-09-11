<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request para atualização de cargo.
 */
class UpdateCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Regras de validação com isolamento de tenant.
     * Rule::exists()->where('empresa_id', ...) impede que departamentos ou cargos
     * de outras empresas sejam vinculados (Cross-Tenant Data Leak).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'nome'              => 'required|string|max:255',
            'descricao'         => 'nullable|string|max:1000',
            'cbo'               => 'nullable|string|max:20',
            'senioridade'       => 'nullable|string|max:255',
            'departamento_id'   => [
                'nullable',
                Rule::exists('departamentos', 'id')->where('empresa_id', $empresaId),
            ],
            'cargo_superior_id' => [
                'nullable',
                Rule::exists('cargos', 'id')->where('empresa_id', $empresaId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required'            => 'O nome do cargo é obrigatório.',
            'departamento_id.exists'   => 'O departamento selecionado não pertence a esta empresa.',
            'cargo_superior_id.exists' => 'O cargo superior selecionado não pertence a esta empresa.',
        ];
    }
}
