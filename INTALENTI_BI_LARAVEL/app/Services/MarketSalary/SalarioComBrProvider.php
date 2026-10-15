<?php

namespace App\Services\MarketSalary;

use App\Models\MarketSalary;
use App\Services\CargoNormalizerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class SalarioComBrProvider
{
    public function __construct(
        private CargoNormalizerService $normalizer,
        private StackTagExtractor $stacks,
        private CargoSpecificity $specificity,
    ) {}

    /**
     * @return array{quote: SalaryQuote, is_specific: bool, url: string, cargo_normalizado: string}|null
     */
    public function fetch(MarketSalary $market): ?array
    {
        $cargoNormalizado = $this->normalizer->normalizeForSearch($market->cargo);
        $stack = $this->stacks->primary($market->competencias_chave);
        $regiaoSlug = Str::slug((string) ($market->regiao ?? ''));

        $url = $this->buildUrl($cargoNormalizado, $stack, $regiaoSlug);

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('Salario.com.br HTTP falhou: '.$e->getMessage(), [
                'url' => $url,
                'cargo' => $market->cargo,
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Salario.com.br status '.$response->status(), ['url' => $url]);

            return null;
        }

        $html = (string) $response->body();
        $mediana = $this->parseMediana($html);

        if (! $mediana || $mediana < 500) {
            return null;
        }

        $isSpecific = $this->pageMatchesRequestedRole($html, $market->cargo, $stack);

        $quote = new SalaryQuote(
            key: 'caged',
            nome: 'Salario.com.br (CAGED)',
            piso: round($mediana * 0.55, 2),
            mediana: $mediana,
            teto: round($mediana * 1.80, 2),
            origem: 'scrape',
            meta: ['url' => $url, 'is_specific' => $isSpecific],
        );

        return [
            'quote' => $quote,
            'is_specific' => $isSpecific,
            'url' => $url,
            'cargo_normalizado' => $cargoNormalizado,
        ];
    }

    public function buildUrl(string $cargoNormalizado, ?string $stack, string $regiaoSlug): string
    {
        $parts = [Str::slug($cargoNormalizado)];
        if ($stack) {
            $parts[] = Str::slug($stack);
        }
        if ($regiaoSlug !== '') {
            $parts[] = $regiaoSlug;
        }

        return 'https://www.salario.com.br/profissao/'.implode('-', $parts);
    }

    public function parseMediana(string $html): ?float
    {
        if (preg_match('/sal[aá]rio\s+m[eé]d(?:io|iana)[^R$]{0,80}R\$\s*([\d\.]+,\d{2})/iu', $html, $matches)) {
            return $this->cleanMoney($matches[1]);
        }

        if (preg_match('/m[eé]dia(?:\s+salarial)?[^R$]{0,60}R\$\s*([\d\.]+,\d{2})/iu', $html, $matches)) {
            return $this->cleanMoney($matches[1]);
        }

        $crawler = new Crawler($html);
        $candidates = [];

        $crawler->filter('body')->each(function (Crawler $node) use (&$candidates) {
            $text = $node->text('');
            if (preg_match_all('/R\$\s*([\d\.]+,\d{2})/u', $text, $all)) {
                foreach ($all[1] as $raw) {
                    $value = $this->cleanMoney($raw);
                    if ($value >= 800 && $value <= 80000) {
                        $candidates[] = $value;
                    }
                }
            }
        });

        if ($candidates === []) {
            return null;
        }

        $counts = array_count_values(array_map(fn ($v) => (string) $v, $candidates));
        arsort($counts);
        $top = array_key_first($counts);

        return (float) $top;
    }

    public function pageMatchesRequestedRole(string $html, string $cargo, ?string $stack): bool
    {
        $crawler = new Crawler($html);
        $title = '';
        $h1 = '';
        try {
            $title = $crawler->filter('title')->count() ? $crawler->filter('title')->text('') : '';
        } catch (\Throwable) {
        }
        try {
            $h1 = $crawler->filter('h1')->count() ? $crawler->filter('h1')->text('') : '';
        } catch (\Throwable) {
        }

        return $this->specificity->pageMatches($title.' '.$h1, $cargo, $stack);
    }

    public function pageMatchesStack(string $html, ?string $stack): bool
    {
        return $this->pageMatchesRequestedRole($html, '', $stack);
    }

    private function cleanMoney(string $string): float
    {
        $cleaned = str_replace(['R$', '.', ' '], '', $string);
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }
}
