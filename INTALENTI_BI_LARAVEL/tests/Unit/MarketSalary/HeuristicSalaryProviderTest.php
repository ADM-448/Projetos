<?php

namespace Tests\Unit\MarketSalary;

use App\Models\MarketSalary;
use App\Services\MarketSalary\HeuristicSalaryProvider;
use App\Services\MarketSalary\StackTagExtractor;
use PHPUnit\Framework\TestCase;

class HeuristicSalaryProviderTest extends TestCase
{
    public function test_java_and_python_get_different_medians(): void
    {
        $provider = new HeuristicSalaryProvider(new StackTagExtractor);

        $java = new MarketSalary([
            'cargo' => 'Desenvolvedor',
            'competencias_chave' => ['Java'],
        ]);
        $python = new MarketSalary([
            'cargo' => 'Desenvolvedor',
            'competencias_chave' => ['Python'],
        ]);

        $this->assertNotEquals(
            $provider->estimateMediana($java),
            $provider->estimateMediana($python)
        );
    }
}
