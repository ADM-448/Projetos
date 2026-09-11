<?php

namespace Tests\Unit;

use App\Enums\StatusPdi;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\ReuniaoAgendamento;
use App\Models\ReuniaoFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testes unitários para o modelo ReuniaoFeedback.
 */
class ReuniaoFeedbackModelTest extends TestCase
{
    use RefreshDatabase;

    private function criarCenarioCompleto(): array
    {
        $empresa     = Empresa::factory()->create();
        $gestor      = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'gestor']);
        $colaborador = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'colaborador']);

        $reuniao = ReuniaoAgendamento::create([
            'gestor_id'      => $gestor->id,
            'colaborador_id' => $colaborador->id,
            'titulo'         => '1:1 Trim 2026',
            'data_hora'      => now()->addDay(),
            'status'         => 'concluido',
        ]);

        $feedback = ReuniaoFeedback::create([
            'gestor_id'            => $gestor->id,
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Transcrição completa da conversa de feedback.',
            'resumo_ia'            => 'Resumo executivo gerado pelo Gemini.',
            'sentimento_ia'        => 'Positivo',
            'audio_path'           => null,
        ]);

        return compact('empresa', 'gestor', 'colaborador', 'reuniao', 'feedback');
    }

    // =========================================================================
    // Estrutura do model
    // =========================================================================

    #[Test]
    public function fillable_contem_os_campos_esperados(): void
    {
        $model    = new ReuniaoFeedback();
        $expected = [
            'gestor_id', 'agendamento_id', 'transcricao_completa', 'transcricao_original',
            'resumo_ia', 'resumo_ia_original', 'sentimento_ia', 'audio_path',
        ];

        $this->assertEqualsCanonicalizing($expected, $model->getFillable());
    }

    #[Test]
    public function usa_tabela_correta_no_banco(): void
    {
        $this->assertEquals('reuniao_feedbacks', (new ReuniaoFeedback())->getTable());
    }

    // =========================================================================
    // Relacionamentos Eloquent
    // =========================================================================

    #[Test]
    public function relacionamento_gestor_retorna_user(): void
    {
        ['feedback' => $feedback, 'gestor' => $gestor] = $this->criarCenarioCompleto();

        $this->assertInstanceOf(User::class, $feedback->gestor);
        $this->assertEquals($gestor->id, $feedback->gestor->id);
    }

    #[Test]
    public function relacionamento_agendamento_retorna_reuniao(): void
    {
        ['feedback' => $feedback, 'reuniao' => $reuniao] = $this->criarCenarioCompleto();

        $this->assertInstanceOf(ReuniaoAgendamento::class, $feedback->agendamento);
        $this->assertEquals($reuniao->id, $feedback->agendamento->id);
    }

    #[Test]
    public function relacionamento_participantes_retorna_colecao_vazia_por_padrao(): void
    {
        ['feedback' => $feedback] = $this->criarCenarioCompleto();

        $this->assertCount(0, $feedback->participantes);
    }

    #[Test]
    public function sync_de_participantes_persiste_no_banco(): void
    {
        ['feedback' => $feedback, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarCenarioCompleto();

        $feedback->participantes()->sync([$gestor->id, $colaborador->id]);

        $this->assertCount(2, $feedback->fresh()->participantes);
        $this->assertDatabaseHas('reuniao_feedback_participantes', [
            'reuniao_feedback_id' => $feedback->id,
            'user_id'             => $gestor->id,
        ]);
        $this->assertDatabaseHas('reuniao_feedback_participantes', [
            'reuniao_feedback_id' => $feedback->id,
            'user_id'             => $colaborador->id,
        ]);
    }

    #[Test]
    public function relacionamento_pdis_retorna_colecao_vazia_por_padrao(): void
    {
        ['feedback' => $feedback] = $this->criarCenarioCompleto();

        $this->assertCount(0, $feedback->pdis);
    }

    #[Test]
    public function relacionamento_pdis_retorna_pdis_vinculados(): void
    {
        ['feedback' => $feedback, 'gestor' => $gestor, 'colaborador' => $colaborador, 'empresa' => $empresa] = $this->criarCenarioCompleto();

        Pdi::create([
            'empresa_id'          => $empresa->id,
            'colaborador_id'      => $colaborador->id,
            'gestor_id'           => $gestor->id,
            'reuniao_feedback_id' => $feedback->id,
            'titulo'              => 'Melhorar Comunicação',
            'status'              => StatusPdi::NAO_INICIADO,
            'progresso'           => 0,
        ]);

        Pdi::create([
            'empresa_id'          => $empresa->id,
            'colaborador_id'      => $colaborador->id,
            'gestor_id'           => $gestor->id,
            'reuniao_feedback_id' => $feedback->id,
            'titulo'              => 'Aperfeiçoar Liderança',
            'status'              => StatusPdi::NAO_INICIADO,
            'progresso'           => 0,
        ]);

        $this->assertCount(2, $feedback->fresh()->pdis);
    }

    // =========================================================================
    // Persistência e exclusão
    // =========================================================================

    #[Test]
    public function persiste_feedback_no_banco_com_campos_corretos(): void
    {
        ['feedback' => $feedback, 'reuniao' => $reuniao] = $this->criarCenarioCompleto();

        $this->assertDatabaseHas('reuniao_feedbacks', [
            'id'             => $feedback->id,
            'agendamento_id' => $reuniao->id,
            'sentimento_ia'  => 'Positivo',
        ]);
    }

    #[Test]
    public function exclui_feedback_do_banco(): void
    {
        ['feedback' => $feedback] = $this->criarCenarioCompleto();
        $id = $feedback->id;

        $feedback->delete();

        $this->assertDatabaseMissing('reuniao_feedbacks', ['id' => $id]);
    }

    #[Test]
    public function updated_at_e_atualizado_ao_usar_touch(): void
    {
        ['feedback' => $feedback] = $this->criarCenarioCompleto();

        $timestampAnterior = $feedback->updated_at;

        sleep(1);
        $feedback->touch();
        $feedback->refresh();

        $this->assertTrue($feedback->updated_at->gt($timestampAnterior));
    }
}
