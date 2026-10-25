<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VagaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isEmpresa() || auth()->user()->isAdmin());
    }

    protected function prepareForValidation(): void
    {
        $sanitize = function ($value): ?float {
            if ($value === null || $value === '') {
                return null;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            if (!is_string($value)) {
                return null;
            }
            $clean = trim(str_replace(['R$', ' '], '', $value));
            if ($clean === '') {
                return null;
            }
            if (str_contains($clean, ',') && str_contains($clean, '.')) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } elseif (str_contains($clean, ',')) {
                $clean = str_replace(',', '.', $clean);
            }
            return is_numeric($clean) ? (float) $clean : null;
        };

        if ($this->has('faixa_salarial_min')) {
            $this->merge(['faixa_salarial_min' => $sanitize($this->faixa_salarial_min)]);
        }

        if ($this->has('faixa_salarial_max')) {
            $this->merge(['faixa_salarial_max' => $sanitize($this->faixa_salarial_max)]);
        }
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_contrato' => ['required', 'string', 'max:100'], // CLT, PJ, Estágio, Jovem Aprendiz, 1° Emprego, etc.
            'modalidade' => ['required', 'in:Presencial,Híbrido,Remoto'],
            'localizacao' => ['required', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'ramo_atividade' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string'],
            'requisitos' => ['nullable', 'string'],
            'beneficios' => ['nullable', 'string'],
            'tipo_vaga' => ['nullable', 'string', 'max:100'],
            'experiencia' => ['nullable', 'string', 'max:100'],
            'faixa_salarial_min' => ['nullable', 'numeric', 'min:0'],
            'faixa_salarial_max' => ['nullable', 'numeric', 'gte:faixa_salarial_min'],
            'salario_a_combinar' => ['nullable', 'boolean'],
            'confidencial' => ['nullable', 'boolean'],
            'perguntas' => ['nullable', 'array'],
            'perguntas.*.texto' => ['required_with:perguntas', 'string'],
            'perguntas.*.eliminatoria' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título da vaga é obrigatório.',
            'tipo_contrato.required' => 'Selecione o tipo de contrato.',
            'modalidade.required' => 'Selecione a modalidade de trabalho.',
            'localizacao.required' => 'A localização da vaga é obrigatória.',
            'ramo_atividade.required' => 'Selecione o ramo de atividade.',
            'descricao.required' => 'A descrição da vaga é obrigatória.',
            'faixa_salarial_max.gte' => 'O salário máximo não pode ser menor que o salário mínimo.',
        ];
    }
}
