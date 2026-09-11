<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\DiscAssessment;
use App\Models\DiscQuestion;
use App\Models\DiscOption;

class DiscAssessmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_disc_test()
    {
        $user = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);

        $response = $this->actingAs($user)->get(route('disc.take'));

        $response->assertStatus(200);
        $response->assertViewIs('disc.take');
    }

    public function test_user_redirected_if_already_taken_disc()
    {
        $user = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);
        $assessment = DiscAssessment::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('disc.take'));

        $response->assertRedirect(route('disc.result', $assessment->id));
        $response->assertSessionHas('info');
    }

    public function test_user_cannot_view_others_disc_results()
    {
        // Use perfil_acesso from the enum or a generic string.
        $user1 = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);
        $user2 = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);

        $assessment = DiscAssessment::create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('disc.result', $assessment->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_others_disc_results()
    {
        $user1 = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);
        $admin = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::ADMIN]);

        $assessment = DiscAssessment::create(['user_id' => $user1->id]);

        $response = $this->actingAs($admin)->get(route('disc.result', $assessment->id));

        $response->assertStatus(200);
    }

    public function test_master_can_view_equipe_page()
    {
        $master = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::MASTER]);

        $response = $this->actingAs($master)->get(route('disc.equipe'));
        $response->assertStatus(200);
        $response->assertViewIs('disc.equipe');
    }

    public function test_colaborador_cannot_view_equipe_page()
    {
        $colaborador = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);

        $response = $this->actingAs($colaborador)->get(route('disc.equipe'));
        $response->assertStatus(403);
    }

    public function test_user_can_submit_disc_assessment()
    {
        $user = User::factory()->create(['perfil_acesso' => \App\Enums\UserRole::COLABORADOR]);
        
        $question = \App\Models\DiscQuestion::create([
            'block_number' => 1,
            'title' => 'Bloco 1'
        ]);

        $opt1 = \App\Models\DiscOption::create(['disc_question_id' => $question->id, 'text' => 'A', 'profile' => 'DOMINANCE']);
        $opt2 = \App\Models\DiscOption::create(['disc_question_id' => $question->id, 'text' => 'B', 'profile' => 'INFLUENCE']);
        $opt3 = \App\Models\DiscOption::create(['disc_question_id' => $question->id, 'text' => 'C', 'profile' => 'STEADINESS']);
        $opt4 = \App\Models\DiscOption::create(['disc_question_id' => $question->id, 'text' => 'D', 'profile' => 'COMPLIANCE']);

        // Simula o request com respostas validas para 1 bloco de perguntas
        $responses = [
            $question->id => [
                $opt1->id => 1,
                $opt2->id => 2,
                $opt3->id => 3,
                $opt4->id => 4,
            ]
        ];

        $response = $this->actingAs($user)->post(route('disc.store'), [
            'responses' => $responses
        ]);

        $this->assertDatabaseHas('disc_assessments', [
            'user_id' => $user->id
        ]);
        
        $assessment = DiscAssessment::where('user_id', $user->id)->first();
        $response->assertRedirect(route('disc.result', $assessment->id));
    }
}
