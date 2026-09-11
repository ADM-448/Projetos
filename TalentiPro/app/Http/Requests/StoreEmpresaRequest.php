<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEmpresaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas ADMIN pode criar empresa.
        // Já protegido via policy no controller, mas reforçamos.
        return $this->user() && $this->user()->perfil_acesso === UserRole::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Dados da Empresa
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', 'max:20', 'unique:empresas,cnpj'],
            'razao_social' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'max_usuarios' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['ativa', 'suspensa'])],
            
            // Dados do Master Inicial
            'master_name' => ['required', 'string', 'max:255'],
            'master_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'master_password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
