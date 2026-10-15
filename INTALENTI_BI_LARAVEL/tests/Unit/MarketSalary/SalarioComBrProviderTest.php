<?php

namespace Tests\Unit\MarketSalary;

use App\Services\CargoNormalizerService;
use App\Services\MarketSalary\SalarioComBrProvider;
use App\Services\MarketSalary\StackTagExtractor;
use PHPUnit\Framework\TestCase;

class SalarioComBrProviderTest extends TestCase
{
    private function provider(): SalarioComBrProvider
    {
        return new SalarioComBrProvider(new CargoNormalizerService, new StackTagExtractor);
    }

    public function test_url_includes_stack_and_region(): void
    {
        $url = $this->provider()->buildUrl('desenvolvedor', 'java', 'sp');

        $this->assertSame('https://www.salario.com.br/profissao/desenvolvedor-java-sp', $url);
    }

    public function test_parse_mediana_uses_salario_medio_not_first_currency(): void
    {
        $html = <<<'HTML'
        <html><head><title>Desenvolvedor Java</title></head>
        <body>
            <p>Banner R$ 10,00</p>
            <p>Salário médio no Brasil: R$ 12.340,00</p>
            <p>Outro valor R$ 1.000,00</p>
        </body></html>
        HTML;

        $this->assertSame(12340.0, $this->provider()->parseMediana($html));
    }

    public function test_generic_page_is_detected_when_stack_missing_from_title(): void
    {
        $html = '<html><head><title>Salário de Desenvolvedor em SP</title></head><body><h1>Desenvolvedor</h1></body></html>';

        $this->assertFalse($this->provider()->pageMatchesStack($html, 'java'));
        $this->assertTrue($this->provider()->pageMatchesStack($html, null));
    }
}
