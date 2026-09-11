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

        foreach ($responses as $response) {
            $profile = $response->option->profile;
            $scores[$profile] += $response->score;
        }

        $assessment->score_d = $scores[DiscProfileEnum::DOMINANCE->value];
        $assessment->score_i = $scores[DiscProfileEnum::INFLUENCE->value];
        $assessment->score_s = $scores[DiscProfileEnum::STEADINESS->value];
        $assessment->score_c = $scores[DiscProfileEnum::COMPLIANCE->value];

        arsort($scores);
        $keys = array_keys($scores);

        $assessment->primary_profile = $keys[0];
        $assessment->secondary_profile = $keys[1];

        $assessment->save();
    }
}
