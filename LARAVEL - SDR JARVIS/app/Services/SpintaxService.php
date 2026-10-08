<?php

namespace App\Services;

class SpintaxService
{
    /**
     * Processa o texto com Spintax aninhado ou simples e interpola variáveis.
     */
    public function parse(string $text, array $variables = []): string
    {
        // 1. Substitui variáveis (ex: {nome} -> Carlos)
        $text = $this->replaceVariables($text, $variables);

        // 2. Resolve blocos de spintax {opcao1|opcao2|opcao3}
        while (preg_match('/\{([^{}]+)\}/', $text, $matches)) {
            $options = explode('|', $matches[1]);
            $selected = $options[array_rand($options)];
            $text = str_replace($matches[0], $selected, $text);
        }

        return trim($text);
    }

    /**
     * Alias para parse()
     */
    public function process(string $text, array $variables = []): string
    {
        return $this->parse($text, $variables);
    }

    /**
     * Substituição segura de tags.
     */
    private function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $text = str_ireplace('{'.$key.'}', (string) $value, $text);
            }
        }

        return $text;
    }
}
