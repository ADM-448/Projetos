<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvaliacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('avaliacao'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $empresaId = $this->user()->empresa_id;
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'grau' => ['required', 'string', 'in:90,180,360'],
            'descricao' => ['nullable', 'string'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'competencias' => ['required', 'array', 'min:1'],
            'competencias.*' => ['exists:competencias,id,empresa_id,' . $empresaId],
            'avaliadores' => ['required', 'array', 'min:1'],
            'avaliadores.*' => ['exists:users,id,empresa_id,' . $empresaId],
            'avaliados' => ['required', 'array', 'min:1'],
            'avaliados.*' => [
                'exists:users,id,empresa_id,' . $empresaId,
                function ($attribute, $value, $fail) {
                    if ($this->user()->perfil_acesso->value === 'gestor') {
                        $colab = \App\Models\User::where('id', $value)->first();
                        if ($colab && $colab->departamento_id !== null && $colab->departamento_id !== $this->user()->departamento_id) {
                            $fail('O colaborador avaliado selecionado deve pertencer ao seu departamento ou a nenhum departamento.');
                        }
                    }
                }
            ],
            'mediadores' => ['nullable', 'array'],
            'mediadores.*' => ['exists:users,id,empresa_id,' . $empresaId],
            'filtros' => ['nullable', 'array'],
        ];
    }
}
