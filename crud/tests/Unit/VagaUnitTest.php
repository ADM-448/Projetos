<?php

namespace Tests\Unit;

use App\Models\Vaga;
use PHPUnit\Framework\TestCase;

class VagaUnitTest extends TestCase
{
    public function test_vaga_is_premium_when_salario_is_10000_or_more(): void
    {
        $vagaHighSalary = new Vaga(['salario' => 12000.00]);
        $vagaExactSalary = new Vaga(['salario' => 10000.00]);

        $this->assertTrue($vagaHighSalary->isPremium());
        $this->assertTrue($vagaExactSalary->isPremium());
    }

    public function test_vaga_is_not_premium_when_salario_is_less_than_10000(): void
    {
        $vagaLowerSalary = new Vaga(['salario' => 8500.00]);

        $this->assertFalse($vagaLowerSalary->isPremium());
    }

    public function test_vaga_handles_null_salario_gracefully_in_is_premium(): void
    {
        $vagaNoSalary = new Vaga(['salario' => null]);

        $this->assertFalse($vagaNoSalary->isPremium());
    }
}
