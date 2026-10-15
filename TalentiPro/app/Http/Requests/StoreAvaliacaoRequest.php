<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAvaliacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Avaliacao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $empresaId = $this->user()->empresa_id;
        return [
            'template_id' => ['nullable', 'exists:avaliacoes,id,empresa_id,' . $empresaId],
            'titulo' => ['required', 'string', 'max:255'],
            'grau' => ['nullable', 'string', 'in:90,180,360'],
            'recorrencia' => ['nullable', 'string', 'in:none,diario,semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual'],
            'descricao' => ['nullable', 'string'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'competencias' => ['nullable', 'array'],
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
