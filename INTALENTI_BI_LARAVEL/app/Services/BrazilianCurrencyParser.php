<?php

namespace App\Services;

class BrazilianCurrencyParser
{
    /**
     * Converte uma string no formato brasileiro (R$ 1.500,00) para float (1500.00).
     */
    public function parse($val): float
    {
        if ($val === null || $val === '') return 0.0;
        
        if (is_float($val) || is_int($val)) {
            $parsed = floatval($val);
            if ($parsed > 0 && $parsed < 100) {
                // Algumas planilhas podem vir com 1.5 ao invés de 1500
                $parsed = $parsed * 1000;
            }
            return $parsed;
        }
        
        $val = trim(str_replace(['R$', ' ', "\xc2\xa0"], '', (string)$val));
        
        $dotCount = substr_count($val, '.');
        $commaCount = substr_count($val, ',');

        if ($commaCount > 0 && $dotCount > 0) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif ($commaCount > 0) {
            $val = str_replace(',', '.', $val);
        } elseif ($dotCount > 0) {
            if (preg_match('/^\d+\.\d{3}$/', $val) || preg_match('/^\d+\.\d{3},\d{2}$/', $val)) {
                $val = str_replace('.', '', $val);
            }
        }
        
        $parsed = floatval($val);
        if ($parsed > 0 && $parsed < 100) {
            $parsed = $parsed * 1000;
        }
        
        return $parsed;
    }
}
