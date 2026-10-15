<?php

namespace App\Services;

class CargoNormalizerService
{
    /**
     * Normaliza nomes de cargos para pesquisa (remove acentos, pontuação e padroniza termos).
     */
    public function normalize(string $name): string
    {
        $unwanted = ['Š'=>'S','š'=>'s','Ž'=>'Z','ž'=>'z','À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Æ'=>'A','Ç'=>'C','È'=>'E','É'=>'E',
                     'Ê'=>'E','Ë'=>'E','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O','Ù'=>'U',
                     'Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','Þ'=>'B','ß'=>'Ss','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'a','ç'=>'c',
                     'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ð'=>'o','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o',
                     'ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ý'=>'y','þ'=>'b','ÿ'=>'y'];
        $name = strtr($name, $unwanted);
        $name = strtolower(preg_replace('/[^a-zA-Z0-9 ]/', ' ', $name));
        $name = trim(preg_replace('/\s+/', ' ', $name));
        
        if (strpos(str_replace(' ', '', $name), 'servgerais') !== false) return 'servicos gerais';
        
        return $name;
    }

    /**
     * Expande abreviações comuns de cargos em português para termos de mercado completos.
     */
    public function normalizeForSearch(string $cargoName): string
    {
        $name = mb_strtolower($cargoName);
        $name = str_replace('.', ' ', $name);

        $replacements = [
            '/\b(ana|anal)\b/u' => 'analista',
            '/\b(enc|encarr)\b/u' => 'encarregado',
            '/\b(aux)\b/u' => 'auxiliar',
            '/\b(ass|assist)\b/u' => 'assistente',
            '/\b(sup|superv)\b/u' => 'supervisor',
            '/\b(coord)\b/u' => 'coordenador',
            '/\b(ger)\b/u' => 'gerente',
            '/\b(dev)\b/u' => 'desenvolvedor',
            '/\b(prog)\b/u' => 'programador',
            '/\b(serv)\b/u' => 'servicos',
            '/\b(fin)\b/u' => 'financeiro',
            '/\b(ctas)\b/u' => 'contas',
            '/\b(paga)\b/u' => 'pagar',
            '/\b(rec)\b/u' => 'receber',
            '/\b(exped)\b/u' => 'expedicao',
            '/\b(op)\b/u' => 'operador',
            '/\b(tec|tecn)\b/u' => 'tecnico',
        ];

        foreach ($replacements as $pattern => $replace) {
            $name = preg_replace($pattern, $replace, $name);
        }

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
