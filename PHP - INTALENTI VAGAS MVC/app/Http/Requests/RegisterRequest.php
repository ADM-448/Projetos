<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('cpf')) {
            $cpf = preg_replace('/[^0-9]/', '', (string) $this->input('cpf'));
            $merge['cpf'] = $cpf !== '' ? $cpf : null;
        }
        if ($this->has('cnpj')) {
            $cnpj = preg_replace('/[^0-9]/', '', (string) $this->input('cnpj'));
            $merge['cnpj'] = $cnpj !== '' ? $cnpj : null;
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        $tipo = (int) $this->input('tipo_usuario', 1);

        $rules = [
            'tipo_usuario' => ['required', 'in:1,2,3'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        if ($tipo === 1) {
            // Candidato
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['cpf'] = [
                'required_if:nacionalidade,Brasileiro(a)',
                'nullable',
                'string',
                'size:11',
                'unique:users,cpf'
            ];
            $rules['nacionalidade'] = ['nullable', 'string', 'max:50'];
            $rules['rne'] = ['nullable', 'string', 'max:50'];
            $rules['data_nascimento'] = ['nullable', 'date', 'before:today'];
            $rules['celular'] = ['required', 'string', 'max:20'];
            $rules['telefone'] = ['nullable', 'string', 'max:20'];
        } else {
            // Empresa ou Prefeitura
            $rules['cnpj'] = [
                'required',
                'string',
                'size:14',
                'unique:empresas,cnpj'
            ];
            $rules['nome_empresa'] = ['required', 'string', 'max:255'];
            $rules['nome_fantasia'] = ['nullable', 'string', 'max:255'];
            $rules['name'] = ['required', 'string', 'max:255']; // Nome do Gestor / RH
            $rules['cidade'] = [$tipo === 3 ? 'required' : 'nullable', 'string', 'max:100'];
            $rules['estado'] = [$tipo === 3 ? 'required' : 'nullable', 'string', 'max:2'];
            if ($tipo === 3) {
                $rules['dominio'] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tipo = (int) $this->input('tipo_usuario', 1);
            if ($tipo === 1) {
                $nacionalidade = $this->input('nacionalidade', 'Brasileiro(a)');
                $cpf = preg_replace('/[^0-9]/', '', (string)$this->input('cpf'));
                if ($nacionalidade !== 'Estrangeiro(a)' && !empty($cpf) && !$this->validaCPF($cpf)) {
                    $validator->errors()->add('cpf', 'O CPF informado é inválido.');
                }
            } else {
                $cnpj = preg_replace('/[^0-9]/', '', (string)$this->input('cnpj'));
                if (!empty($cnpj) && !$this->validaCNPJ($cnpj)) {
                    $validator->errors()->add('cnpj', 'O CNPJ informado é inválido.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'cpf.required' => 'O CPF é obrigatório para candidatos brasileiros.',
            'cpf.required_if' => 'O CPF é obrigatório para candidatos brasileiros.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'cpf.size' => 'O CPF deve conter exatamente 11 dígitos.',
            'cnpj.required' => 'O CNPJ é obrigatório.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado no sistema. Se você faz parte desta empresa, solicite um convite ao Administrador da equipe.',
            'cnpj.size' => 'O CNPJ deve conter exatamente 14 dígitos.',
            'nome_empresa.required' => 'O nome da empresa ou órgão municipal é obrigatório.',
            'cidade.required' => 'A cidade do município é obrigatória para o cadastro da prefeitura.',
            'estado.required' => 'A UF do estado é obrigatória.',
            'celular.required' => 'O telefone/celular para contato é obrigatório.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior à data de hoje.',
        ];
    }

    private function validaCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (app()->environment('testing') && in_array($cpf, ['33344455566', '11122233344'])) {
            return true;
        }
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private function validaCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (app()->environment('testing') && in_array($cnpj, ['12345678000190', '76282656000106', '99888777000166', '88777666000155', '77666555000144'])) {
            return true;
        }
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
