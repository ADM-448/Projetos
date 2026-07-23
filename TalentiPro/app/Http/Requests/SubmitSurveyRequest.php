<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $surveyId = $this->route('id');

        return [
            'respostas'                  => ['required', 'array', 'min:1'],

            // question_id deve existir E pertencer à survey sendo respondida (fix auditoria #6)
            'respostas.*.question_id'    => [
                'required',
                'integer',
                Rule::exists('climate_questions', 'id')
                    ->where('survey_id', $surveyId),
            ],

            // Para perguntas tipo nota: inteiro de 1 a 10
            'respostas.*.nota'           => ['nullable', 'integer', 'min:1', 'max:10'],

            // Para perguntas tipo texto: string livre, máximo 2000 chars
            'respostas.*.texto_resposta' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'respostas.required'                  => 'É necessário enviar ao menos uma resposta.',
            'respostas.*.question_id.exists'       => 'Uma ou mais perguntas não pertencem a esta pesquisa.',
            'respostas.*.nota.min'                 => 'A nota deve ser entre 1 e 10.',
            'respostas.*.nota.max'                 => 'A nota deve ser entre 1 e 10.',
            'respostas.*.texto_resposta.max'       => 'A resposta de texto não pode ultrapassar 2.000 caracteres.',
        ];
    }
}
