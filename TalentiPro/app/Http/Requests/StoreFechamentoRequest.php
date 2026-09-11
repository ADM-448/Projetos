<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFechamentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('mediar', $this->route('avaliacao'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'respostas' => ['required', 'array', 'min:1'],
            'respostas.*' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['required', 'string'],
        ];
    }
}
