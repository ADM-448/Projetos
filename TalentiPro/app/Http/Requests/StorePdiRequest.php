<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePdiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Controlado pelas Policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_criacao' => ['required', 'string', 'in:individual,grupo'],
            'colaborador_id' => ['required_if:tipo_criacao,individual', 'nullable', 'exists:users,id'],
            'colaboradores' => ['required_if:tipo_criacao,grupo', 'array', 'min:1'],
            'colaboradores.*' => ['exists:users,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'gerado_ia' => ['nullable', 'boolean'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'acoes' => ['required', 'array', 'min:1'],
            'acoes.*.id' => ['nullable', 'integer'],
            'acoes.*.original_titulo' => ['nullable', 'string', 'max:255'],
            'acoes.*.titulo' => ['required', 'string', 'max:255'],
            'acoes.*.descricao' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tipo_criacao' => 'tipo de criação',
            'colaborador_id' => 'colaborador',
            'colaboradores' => 'colaboradores selecionados',
            'titulo' => 'título',
            'descricao' => 'descrição',
            'categoria' => 'categoria',
            'data_inicio' => 'data de início',
            'data_fim' => 'data de término',
            'acoes' => 'ações do PDI',
            'acoes.*.titulo' => 'título da ação',
            'acoes.*.descricao' => 'descrição da ação',
        ];
    }
}
