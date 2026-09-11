<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\DiscAssessment;
use App\Models\DiscQuestion;
use App\Models\DiscOption;
use App\Models\DiscResponse;
use App\Enums\DiscProfileEnum;
use App\Services\CalculateDiscProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculateDiscProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_disc_profile_correctly()
    {
        // Setup user and assessment
        $user = User::factory()->create();
        $assessment = DiscAssessment::create(['user_id' => $user->id]);

        // Create 2 questions
        for ($i = 1; $i <= 2; $i++) {
            $question = DiscQuestion::create(['block_number' => $i, 'title' => "Block $i"]);
            
            $optionD = DiscOption::create(['disc_question_id' => $question->id, 'text' => 'D', 'profile' => DiscProfileEnum::DOMINANCE->value]);
            $optionI = DiscOption::create(['disc_question_id' => $question->id, 'text' => 'I', 'profile' => DiscProfileEnum::INFLUENCE->value]);
            $optionS = DiscOption::create(['disc_question_id' => $question->id, 'text' => 'S', 'profile' => DiscProfileEnum::STEADINESS->value]);
            $optionC = DiscOption::create(['disc_question_id' => $question->id, 'text' => 'C', 'profile' => DiscProfileEnum::COMPLIANCE->value]);

            // Give max scores to DOMINANCE and INFLUENCE
            DiscResponse::create(['disc_assessment_id' => $assessment->id, 'disc_option_id' => $optionD->id, 'score' => 4]);
            DiscResponse::create(['disc_assessment_id' => $assessment->id, 'disc_option_id' => $optionI->id, 'score' => 3]);
            DiscResponse::create(['disc_assessment_id' => $assessment->id, 'disc_option_id' => $optionS->id, 'score' => 2]);
            DiscResponse::create(['disc_assessment_id' => $assessment->id, 'disc_option_id' => $optionC->id, 'score' => 1]);
        }

        // Execute service
        $service = new CalculateDiscProfileService();
        $service->execute($assessment);

        $assessment->refresh();

        // 2 questions * 4 = 8
        $this->assertEquals(8, $assessment->score_d);
        // 2 questions * 3 = 6
        $this->assertEquals(6, $assessment->score_i);
        $this->assertEquals(4, $assessment->score_s);
        $this->assertEquals(2, $assessment->score_c);

        $this->assertEquals(DiscProfileEnum::DOMINANCE->value, $assessment->primary_profile);
        $this->assertEquals(DiscProfileEnum::INFLUENCE->value, $assessment->secondary_profile);
    }
}
