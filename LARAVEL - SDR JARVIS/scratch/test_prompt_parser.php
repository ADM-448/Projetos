<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::find(3);
$instructions = $tenant->settings['instructions'] ?? '';
$companyProp = $tenant->settings['company_value_prop'] ?? '';
$agentName = $tenant->settings['ai_name'] ?? 'Consultor';

function parseUserPrompt(string $instructions, string $companyProp): array
{
    $parsed = [
        'company_name' => 'TalentiPro',
        'is_hr' => false,
        'step1_q' => null,
        'step2_q' => null,
        'step3_q' => null,
        'objections' => [],
        'cta' => null,
        'links' => [],
    ];

    $combined = mb_strtolower($instructions . ' ' . $companyProp);
    if (str_contains($combined, 'rh') || str_contains($combined, 'people') || str_contains($combined, 'talenti') || str_contains($combined, 'gestão de pessoas')) {
        $parsed['is_hr'] = true;
    }

    if (preg_match('/talentipro/i', $companyProp . ' ' . $instructions)) {
        $parsed['company_name'] = 'TalentiPro';
    }

    // Extrai perguntas entre aspas
    preg_match_all('/"([^"]+)"/u', $instructions, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $quote) {
            if (str_contains($quote, 'você atua') || str_contains($quote, 'persona') || str_contains($quote, 'liderança')) {
                $parsed['step1_q'] = $quote;
            } elseif (str_contains($quote, 'planilhas') || str_contains($quote, 'ferramenta')) {
                $parsed['step2_q'] = $quote;
            } elseif (str_contains($quote, 'tamanho') || str_contains($quote, 'equipe')) {
                $parsed['step3_q'] = $quote;
            } elseif (str_contains($quote, 'demonstração') || str_contains($quote, 'próximo passo')) {
                $parsed['cta'] = $quote;
            }
        }
    }

    return $parsed;
}

$parsed = parseUserPrompt($instructions, $companyProp);
echo "IS_HR: " . ($parsed['is_hr'] ? 'YES' : 'NO') . "\n";
echo "STEP 1: " . ($parsed['step1_q'] ?? 'none') . "\n";
echo "STEP 2: " . ($parsed['step2_q'] ?? 'none') . "\n";
echo "STEP 3: " . ($parsed['step3_q'] ?? 'none') . "\n";
echo "CTA: " . ($parsed['cta'] ?? 'none') . "\n";
