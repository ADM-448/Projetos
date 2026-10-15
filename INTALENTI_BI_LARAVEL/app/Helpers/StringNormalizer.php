<?php

namespace App\Helpers;

class StringNormalizer
{
    /**
     * Normaliza um nome de cargo ou texto genérico:
     * - Converte para minúsculo
     * - Remove acentos
     * - Remove espaços duplos e laterais
     * 
     * Exemplo: " Operador  de Caixa " -> "operador de caixa"
     * Exemplo: "OPÊRADÓR" -> "operador"
     */
    public static function normalizeCargo(?string $texto): string
    {
        if (!$texto) {
            return '';
        }

        // 1. Minúsculo
        $texto = mb_strtolower($texto, 'UTF-8');

        // 2. Remove acentos
        $texto = preg_replace('/[áàãâä]/ui', 'a', $texto);
        $texto = preg_replace('/[éèêë]/ui', 'e', $texto);
        $texto = preg_replace('/[íìîï]/ui', 'i', $texto);
        $texto = preg_replace('/[óòõôö]/ui', 'o', $texto);
        $texto = preg_replace('/[úùûü]/ui', 'u', $texto);
        $texto = preg_replace('/[ç]/ui', 'c', $texto);

        // 3. Remove caracteres especiais (deixa só letras, números e espaços)
        $texto = preg_replace('/[^a-z0-9\s]/ui', '', $texto);

        // 4. Remove espaços extras e trim
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        return trim($texto);
    }
}
