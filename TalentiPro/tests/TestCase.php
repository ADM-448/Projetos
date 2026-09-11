<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Desabilita verificação de CSRF token em todos os testes.
        // Em testes, as requisições são simuladas sem formulário HTML,
        // portanto não há token CSRF disponível. Isso é seguro porque
        // os testes rodam em ambiente isolado com banco separado.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
