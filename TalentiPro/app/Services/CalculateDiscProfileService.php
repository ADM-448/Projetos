<?php

namespace App\Services;

use App\Models\DiscAssessment;
use App\Models\DiscResponse;
use App\Enums\DiscProfileEnum;

class CalculateDiscProfileService
{
    public function execute(DiscAssessment $assessment): void
    {
        $responses = $assessment->responses()->with('option')->get();

        $scores = [
            DiscProfileEnum::DOMINANCE->value => 0,
            DiscProfileEnum::INFLUENCE->value => 0,
            DiscProfileEnum::STEADINESS->value => 0,
            DiscProfileEnum::COMPLIANCE->value => 0,
        ];
        
        $adaptedRaw = $scores;
        $naturalRaw = $scores;

        foreach ($responses as $response) {
            if (!$response->option) continue;
            
            $profile = $response->option->profile;
            $score = $response->score; // 1, 2, 3, 4
            
            // Geral / Natural (Soma simples de todos os pontos)
            $naturalRaw[$profile] += $score;
            
            // Adaptado (Peso maior para escolhas conscientes 'Most')
            // Se 4 (Mais) = 4 pts. Se 3 = 2 pts. Se 2 ou 1 = 0 pts.
            $adaptedScore = ($score == 4) ? 4 : (($score == 3) ? 2 : 0);
            $adaptedRaw[$profile] += $adaptedScore;
        }

        // Descobrir Total de Blocos
        $totalResponses = $responses->count();
        $totalBlocos = $totalResponses > 0 ? $totalResponses / 4 : 1;
        
        // Ranges
        $minNatural = $totalBlocos * 1;
        $maxNatural = $totalBlocos * 4;
        $rangeNatural = $maxNatural - $minNatural;
        if ($rangeNatural <= 0) $rangeNatural = 1;

        $maxAdapted = $totalBlocos * 4; // Max is if they pick 4 every time
        if ($maxAdapted <= 0) $maxAdapted = 1;

        $normalizedScores = [];
        $adapted100 = [];
        $natural100 = [];

        foreach ($scores as $profile => $ignored) {
            // Natural Normalizado 0-100
            $nat100 = round((($naturalRaw[$profile] - $minNatural) / $rangeNatural) * 100);
            $natural100[$profile] = max(0, min(100, $nat100));
            
            // Adaptado Normalizado 0-100
            $adap100 = round(($adaptedRaw[$profile] / $maxAdapted) * 100);
            $adapted100[$profile] = max(0, min(100, $adap100));
            
            // A pontuação geral (score_x) no banco sempre espelha o Natural
            $normalizedScores[$profile] = $natural100[$profile];
        }

        $assessment->score_d = $normalizedScores[DiscProfileEnum::DOMINANCE->value];
        $assessment->score_i = $normalizedScores[DiscProfileEnum::INFLUENCE->value];
        $assessment->score_s = $normalizedScores[DiscProfileEnum::STEADINESS->value];
        $assessment->score_c = $normalizedScores[DiscProfileEnum::COMPLIANCE->value];

        // Determinar perfil primário e secundário baseados no score Natural Normalizado (Essência real do indivíduo)
        arsort($natural100);
        $keys = array_keys($natural100);

        $assessment->primary_profile = $keys[0] ?? null;
        $assessment->secondary_profile = $keys[1] ?? null;
        
        // ----------------------------------------------------
        // NOVO MOTOR DE ANÁLISE (Gaps, Tensão, Eixos e Subtraços)
        // ----------------------------------------------------
        
        $d = DiscProfileEnum::DOMINANCE->value;
        $i = DiscProfileEnum::INFLUENCE->value;
        $s = DiscProfileEnum::STEADINESS->value;
        $c = DiscProfileEnum::COMPLIANCE->value;
        
        $deltas = [];
        $highStress = false;
        $profilesKeys = [$d, $i, $s, $c];
        
        foreach ($profilesKeys as $f) {
            $delta = $adapted100[$f] - $natural100[$f];
            $deltas[$f] = $delta;
            if (abs($delta) >= 20) {
                $highStress = true;
            }
        }
        
        // Análise de Perfil Comprimido (Todos espremidos entre 40 e 60)
        $compressed = true;
        foreach ($natural100 as $val) {
            if ($val < 35 || $val > 65) {
                $compressed = false;
                break;
            }
        }
        
        // Matriz Dimensional (Extroversão vs Introversão | Tarefas vs Pessoas)
        $totalNatural = array_sum($natural100) ?: 1;
        $focoTarefas = round((($natural100[$d] + $natural100[$c]) / $totalNatural) * 100); 
        $ritmoAtivo = round((($natural100[$d] + $natural100[$i]) / $totalNatural) * 100); 

        // Subtraços (Diferença acentuada >= 15 pontos)
        $subtraits = [];
        $letterMap = [$d => 'D', $i => 'I', $s => 'S', $c => 'C'];
        foreach($profilesKeys as $k1) {
            foreach($profilesKeys as $k2) {
                if ($k1 !== $k2) {
                    if (($natural100[$k1] - $natural100[$k2]) >= 15) {
                        $subtraits[] = $letterMap[$k1] . '_' . $letterMap[$k2];
                    }
                }
            }
        }

        $assessment->metadata = [
            'raw_scores' => $scores,
            'adaptado' => $adapted100,
            'natural' => $natural100,
            'deltas' => $deltas,
            'high_stress_adaptation' => $highStress,
            'compressed_profile' => $compressed,
            'eixos' => [
                'foco_tarefas' => $focoTarefas, 
                'ritmo_ativo' => $ritmoAtivo
            ],
            'subtraits_active' => $subtraits
        ];

        $assessment->save();
    }
}
