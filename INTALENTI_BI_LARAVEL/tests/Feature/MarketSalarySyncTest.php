<?php

namespace Tests\Feature;

use App\Jobs\ScrapeMarketSalaryJob;
use App\Models\MarketSalary;
use App\Services\MarketSalary\MarketContextHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketSalarySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_java_and_python_context_hashes_differ(): void
    {
        $hasher = new MarketContextHasher;

        $java = $hasher->make('Desenvolvedor', 'SP', 'startup', 'tecnologia', ['Java']);
        $python = $hasher->make('Desenvolvedor', 'SP', 'startup', 'tecnologia', ['Python']);

        $this->assertNotSame($java, $python);
    }

    public function test_job_produces_different_medians_for_java_and_python_when_scrape_is_generic(): void
    {
        config(['services.gemini.api_key' => null]);

        Http::fake([
            'www.salario.com.br/*' => Http::response(
                '<html><head><title>Salário de Desenvolvedor em SP</title></head><body><h1>Desenvolvedor</h1><p>Salário médio: R$ 7.500,00</p></body></html>',
                200
            ),
        ]);

        $java = MarketSalary::create([
            'cargo' => 'Desenvolvedor',
            'regiao' => 'SP',
            'porte_empresa' => 'startup',
            'segmento_empresa' => 'tecnologia',
            'competencias_chave' => ['Java'],
            'context_hash' => 'java-hash',
            'status' => 'pending',
        ]);

        $python = MarketSalary::create([
            'cargo' => 'Desenvolvedor',
            'regiao' => 'SP',
            'porte_empresa' => 'startup',
            'segmento_empresa' => 'tecnologia',
            'competencias_chave' => ['Python'],
            'context_hash' => 'python-hash',
            'status' => 'pending',
        ]);

        (new ScrapeMarketSalaryJob($java->id))->handle(app(\App\Services\MarketSalary\MarketSalaryAggregator::class));
        (new ScrapeMarketSalaryJob($python->id))->handle(app(\App\Services\MarketSalary\MarketSalaryAggregator::class));

        $java->refresh();
        $python->refresh();

        $this->assertSame('completed', $java->status);
        $this->assertSame('completed', $python->status);
        $this->assertNotEquals((float) $java->mediana, (float) $python->mediana);
        $this->assertTrue($java->raw_data['used_heuristic']);
        $this->assertTrue($python->raw_data['used_heuristic']);
        $this->assertArrayHasKey('heuristica', $java->raw_data['fontes']);
        $this->assertArrayNotHasKey('glassdoor', $java->raw_data['fontes']);
        $this->assertArrayNotHasKey('indeed', $java->raw_data['fontes']);
    }

    public function test_generic_scrape_without_tags_uses_caged_page(): void
    {
        config(['services.gemini.api_key' => null]);

        Http::fake([
            'www.salario.com.br/*' => Http::response(
                '<html><head><title>Salário de Desenvolvedor em SP</title></head><body><h1>Desenvolvedor</h1><p>Salário médio: R$ 7.500,00</p></body></html>',
                200
            ),
        ]);

        $market = MarketSalary::create([
            'cargo' => 'Desenvolvedor',
            'regiao' => 'SP',
            'competencias_chave' => [],
            'context_hash' => 'plain-dev',
            'status' => 'pending',
        ]);

        (new ScrapeMarketSalaryJob($market->id))->handle(app(\App\Services\MarketSalary\MarketSalaryAggregator::class));

        $market->refresh();

        $this->assertSame('completed', $market->status);
        $this->assertEquals(7500.0, (float) $market->mediana);
        $this->assertFalse($market->raw_data['used_heuristic']);
        $this->assertArrayHasKey('caged', $market->raw_data['fontes']);
    }

    public function test_sync_all_seeds_by_cargo_id_tags_and_returns_seeded_dashboard_rows(): void
    {
        config(['services.gemini.api_key' => null]);

        Http::fake([
            'www.salario.com.br/*' => Http::response(
                '<html><head><title>Salário de Desenvolvedor em SP</title></head><body><h1>Desenvolvedor</h1><p>Salário médio: R$ 7.500,00</p></body></html>',
                200
            ),
        ]);

        $this->withSession([
            'cargos' => [
                ['id' => 1, 'nome' => 'Desenvolvedor'],
            ],
        ])->post(route('dashboard.market-sync-all'), [
            'porte_empresa' => 'startup',
            'segmento_empresa' => 'tecnologia',
            'regiao_empresa' => 'SP',
            'tags' => [
                1 => ['Java'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('market_salaries', [
            'cargo' => 'Desenvolvedor',
            'status' => 'completed',
        ]);

        $row = MarketSalary::first();
        $this->assertEquals(['Java'], $row->competencias_chave);
        $this->assertNotNull($row->raw_data['fontes']);
        $this->assertSame(['Java'], $row->raw_data['tags']);
    }
}
