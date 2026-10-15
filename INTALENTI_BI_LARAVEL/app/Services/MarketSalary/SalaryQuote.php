<?php

namespace App\Services\MarketSalary;

class SalaryQuote
{
    public function __construct(
        public string $key,
        public string $nome,
        public float $piso,
        public float $mediana,
        public float $teto,
        public string $origem,
        public array $meta = [],
    ) {}

    public function toFonteArray(): array
    {
        return [
            'nome' => $this->nome,
            'piso' => round($this->piso, 2),
            'mediana' => round($this->mediana, 2),
            'teto' => round($this->teto, 2),
            'origem' => $this->origem,
        ];
    }
}
