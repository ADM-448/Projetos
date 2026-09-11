<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para atualização de colaborador.
 *
 * Idêntico ao StoreColaboradorRequest com exceção da regra de email
 * que ignora o registro atual (unique:users,email,{id}).
 */
class UpdateColaboradorRequest extends FormRequest
{
    /**
     * Apenas usuários autenticados podem atualizar colaboradores.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepara os dados antes da validação.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->cpf),
            ]);
        }
    }

    /**
     * Regras de validação para atualização de colaborador.
     *
     * @return array<string, string|array<string>>
     */
    public function rules(): array
    {
        // Laravel pluraliza 'colaborador' como 'colaboradore' no resource;
        // usamos o parâmetro correto ou fallback para o primeiro parâmetro da rota
        $colaboradorId = $this->route('colaboradore') ?? $this->route()->parameter('colaboradore');

        return [
            'name'            => 'required|string|max:255',
            'email'           => "nullable|email|max:255|unique:users,email,{$colaboradorId}",
            'cpf'             => "nullable|string|max:14|unique:users,cpf,{$colaboradorId}",
            'telefone'        => 'nullable|string|max:20',
            'password'        => 'nullable|string|min:6',
            'perfil_acesso'   => 'required|string|in:colaborador,gestor,master',
            'status'          => 'required|string|in:ativo,ferias,inativo',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'cargo_id'        => 'nullable|exists:cargos,id',
            'gestor_id'       => 'nullable|exists:users,id',
        ];
    }

    /**
     * Mensagens de erro personalizadas em Português.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'O nome do colaborador é obrigatório.',
            'email.unique'           => 'Este e-mail já está em uso por outro colaborador.',
            'cpf.unique'             => 'Este CPF já está em uso por outro colaborador.',
            'password.min'           => 'A nova senha deve ter no mínimo 6 caracteres.',
            'perfil_acesso.required' => 'O perfil de acesso é obrigatório.',
            'perfil_acesso.in'       => 'O perfil de acesso selecionado é inválido.',
            'status.required'        => 'O status do colaborador é obrigatório.',
            'status.in'              => 'O status selecionado é inválido.',
        ];
    }
}
