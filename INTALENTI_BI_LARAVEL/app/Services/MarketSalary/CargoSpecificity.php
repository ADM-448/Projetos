<?php

namespace App\Services\MarketSalary;

class CargoSpecificity
{
    private const STOP = [
        'para', 'com', 'sem', 'por', 'uma', 'uma', 'de', 'da', 'do', 'das', 'dos',
        'e', 'em', 'a', 'o', 'as', 'os', 'the',
    ];

    private const GENERIC_TITLES = [
        'desenvolvedor', 'programador', 'analista', 'assistente', 'auxiliar',
        'tecnico', 'técnico', 'especialista', 'profissional',
    ];

    /**
     * Tokens that CAGED typically will not distinguish (specialty inside a family).
     *
     * @return list<string>
     */
    public function distinctiveTokens(string $cargo, ?string $stack = null): array
    {
        $normalized = mb_strtolower($cargo);
        $normalized = str_replace(['/', '_'], ' ', $normalized);
        $parts = preg_split('/[\s\-]+/u', $normalized) ?: [];

        $tokens = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || in_array($part, self::STOP, true)) {
                continue;
            }
            if (in_array($part, self::GENERIC_TITLES, true)) {
                continue;
            }
            if (mb_strlen($part) < 3 && ! in_array($part, ['rh', 'ux', 'ui', 'ti', 'ml', 'ai'], true)) {
                continue;
            }
            $tokens[] = $part;
        }

        if ($stack) {
            $tokens[] = mb_strtolower($stack);
        }

        return array_values(array_unique($tokens));
    }

    public function isSpecialized(string $cargo, ?array $tags = null, ?string $stack = null): bool
    {
        return $this->distinctiveTokens($cargo, $stack) !== [];
    }

    public function pageMatches(string $htmlTitleAndH1, string $cargo, ?string $stack = null): bool
    {
        $tokens = $this->distinctiveTokens($cargo, $stack);
        if ($tokens === []) {
            return true;
        }

        $haystack = mb_strtolower($htmlTitleAndH1);
        foreach ($tokens as $token) {
            if (str_contains($haystack, $token)) {
                return true;
            }
        }

        return false;
    }
}
