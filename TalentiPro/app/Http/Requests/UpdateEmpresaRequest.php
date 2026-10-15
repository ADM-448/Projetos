<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->perfil_acesso === UserRole::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // O ID da empresa vem na rota: /admin/empresas/{empresa}
        $empresaId = $this->route('empresa')->id;

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', 'max:20', Rule::unique('empresas')->ignore($empresaId)],
            'razao_social' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'max_usuarios' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['ativa', 'suspensa'])],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
            'remover_logo' => ['nullable', 'boolean'],
            'ia_ativa' => ['nullable', 'boolean'],
            'ia_limite_transcricoes_mes' => ['nullable', 'integer', 'min:0'],
            'whatsapp_ativo' => ['nullable', 'boolean'],
        ];
    }
}
