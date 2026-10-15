<?php

namespace App\Services\MarketSalary;

class MarketContextHasher
{
    /**
     * @param  array<int, string>  $tags
     */
    public function make(string $cargo, string $regiao, string $porte, string $segmento, array $tags): string
    {
        $normalizedTags = array_map(fn ($tag) => mb_strtolower(trim((string) $tag)), $tags);
        $normalizedTags = array_values(array_filter($normalizedTags, fn ($tag) => $tag !== ''));
        sort($normalizedTags);

        $contextString = mb_strtolower(
            $cargo.'|'.$regiao.'|'.$porte.'|'.$segmento.'|'.implode(',', $normalizedTags)
        );

        return md5($contextString);
    }
}
