<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Http\Requests\RegisterRequest;
use ReflectionMethod;

class SecurityValidationTest extends TestCase
{
    /**
     * Valida que o helper do Model User formata e extrai corretamente o primeiro nome.
     */
    public function test_user_primeiro_nome_attribute(): void
    {
        $user = new User(['name' => 'Carlos Alberto da Silva']);
        $this->assertEquals('Carlos', $user->primeiro_nome);

        $singleNameUser = new User(['name' => 'Ademar']);
        $this->assertEquals('Ademar', $singleNameUser->primeiro_nome);
    }

    /**
     * Valida os métodos de papéis e permissões no model User.
     */
    public function test_user_role_helper_methods(): void
    {
        $candidato = new User(['tipo_usuario' => 1]);
        $this->assertTrue($candidato->isCandidato());
        $this->assertFalse($candidato->isEmpresa());
        $this->assertFalse($candidato->isAdmin());

        $empresa = new User(['tipo_usuario' => 2]);
        $this->assertFalse($empresa->isCandidato());
        $this->assertTrue($empresa->isEmpresa());
        $this->assertFalse($empresa->isAdmin());

        $admin = new User(['tipo_usuario' => 3]);
        $this->assertFalse($admin->isCandidato());
        $this->assertFalse($admin->isEmpresa());
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isPrefeitura());
    }

    /**
     * Testa o algoritmo de validação de CPF (dígitos verificadores válidos vs inválidos).
     */
    public function test_cpf_validation_algorithm(): void
    {
        $request = new RegisterRequest();
        $refMethod = new ReflectionMethod(RegisterRequest::class, 'validaCPF');
        $refMethod->setAccessible(true);

        // CPFs matematicamente válidos
        $this->assertTrue($refMethod->invoke($request, '52998224725'));
        $this->assertTrue($refMethod->invoke($request, '529.982.247-25'));

        // CPFs inválidos (dígitos verificadores incorretos)
        $this->assertFalse($refMethod->invoke($request, '12345678900'));
        $this->assertFalse($refMethod->invoke($request, '00011122233'));

        // Sequências repetidas inválidas
        $this->assertFalse($refMethod->invoke($request, '11111111111'));
        $this->assertFalse($refMethod->invoke($request, '22222222222'));
        $this->assertFalse($refMethod->invoke($request, '00000000000'));

        // Tamanho incorreto
        $this->assertFalse($refMethod->invoke($request, '123456789'));
        $this->assertFalse($refMethod->invoke($request, '123456789012'));
    }

    /**
     * Testa o algoritmo de validação de CNPJ (dígitos verificadores válidos vs inválidos).
     */
    public function test_cnpj_validation_algorithm(): void
    {
        $request = new RegisterRequest();
        $refMethod = new ReflectionMethod(RegisterRequest::class, 'validaCNPJ');
        $refMethod->setAccessible(true);

        // CNPJs matematicamente válidos (ex: Banco do Brasil)
        $this->assertTrue($refMethod->invoke($request, '00000000000191'));
        $this->assertTrue($refMethod->invoke($request, '00.000.000/0001-91'));

        // CNPJs com dígitos verificadores incorretos
        $this->assertFalse($refMethod->invoke($request, '00000000000199'));
        $this->assertFalse($refMethod->invoke($request, '12345678000100'));

        // Sequências repetidas inválidas
        $this->assertFalse($refMethod->invoke($request, '00000000000000'));
        $this->assertFalse($refMethod->invoke($request, '11111111111111'));

        // Tamanho incorreto
        $this->assertFalse($refMethod->invoke($request, '123456780001'));
        $this->assertFalse($refMethod->invoke($request, '123456780001999'));
    }

    /**
     * Valida que o prepareForValidation normaliza dados e converte strings vazias para null.
     */
    public function test_prepare_for_validation_sanitization(): void
    {
        $request = new RegisterRequest();
        $refMethod = new ReflectionMethod(RegisterRequest::class, 'prepareForValidation');
        $refMethod->setAccessible(true);

        // Caso 1: CPF e CNPJ com máscaras e pontuações
        $request->replace([
            'cpf' => ' 123.456.789-01 ',
            'cnpj' => ' 12.345.678/0001-90 ',
        ]);
        $refMethod->invoke($request);
        $this->assertEquals('12345678901', $request->input('cpf'));
        $this->assertEquals('12345678000190', $request->input('cnpj'));

        // Caso 2: Campos enviados vazios ou apenas com traços/espaços devem virar NULL
        $requestEmpty = new RegisterRequest();
        $requestEmpty->replace([
            'cpf' => '   ',
            'cnpj' => '---/.-',
        ]);
        $refMethod->invoke($requestEmpty);
        $this->assertNull($requestEmpty->input('cpf'));
        $this->assertNull($requestEmpty->input('cnpj'));
    }
}
