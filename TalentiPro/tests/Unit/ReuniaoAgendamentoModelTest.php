<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\ReuniaoAgendamento;
use App\Models\ReuniaoFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testes unitários para o modelo ReuniaoAgendamento.
 *
 * Cobrem: fillable, casts de datas, relacionamentos Eloquent e
 * regras de domínio verificáveis sem chamadas de rede.
 */
class ReuniaoAgendamentoModelTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function criarReuniao(array $extra = []): ReuniaoAgendamento
    {
        $empresa = Empresa::factory()->create();
        $gestor  = User::factory()->create([
            'empresa_id'    => $empresa->id,
            'perfil_acesso' => 'gestor',
        ]);
        $colaborador = User::factory()->create([
            'empresa_id'    => $empresa->id,
            'perfil_acesso' => 'colaborador',
        ]);

        return ReuniaoAgendamento::create(array_merge([
            'gestor_id'      => $gestor->id,
            'colaborador_id' => $colaborador->id,
            'titulo'         => 'Reunião de Feedback 1:1',
            'data_hora'      => now()->addDay(),
            'status'         => 'agendado',
        ], $extra));
    }

    // =========================================================================
    // Estrutura do model
    // =========================================================================

    #[Test]
    public function fillable_contem_os_campos_esperados(): void
    {
        $model    = new ReuniaoAgendamento();
        $expected = [
            'gestor_id', 'colaborador_id', 'titulo', 'data_hora',
            'google_event_id', 'google_meet_link', 'transcricao_temporaria', 'status',
        ];

        $this->assertEqualsCanonicalizing($expected, $model->getFillable());
    }

    #[Test]
    public function data_hora_eh_castada_como_datetime(): void
    {
        $reuniao = $this->criarReuniao();

        $this->assertInstanceOf(\Carbon\Carbon::class, $reuniao->data_hora);
    }

    // =========================================================================
    // Relacionamentos Eloquent
    // =========================================================================

    #[Test]
    public function relacionamento_gestor_retorna_user_correto(): void
    {
        $reuniao = $this->criarReuniao();

        $this->assertInstanceOf(User::class, $reuniao->gestor);
        $this->assertEquals('gestor', $reuniao->gestor->perfil_acesso->value);
    }

    #[Test]
    public function relacionamento_colaborador_retorna_user_correto(): void
    {
        $reuniao = $this->criarReuniao();

        $this->assertInstanceOf(User::class, $reuniao->colaborador);
        $this->assertEquals('colaborador', $reuniao->colaborador->perfil_acesso->value);
    }

    #[Test]
    public function relacionamento_feedback_retorna_null_quando_nao_existir(): void
    {
        $reuniao = $this->criarReuniao();

        $this->assertNull($reuniao->feedback);
    }

    #[Test]
    public function relacionamento_feedback_retorna_instancia_quando_existir(): void
    {
        $reuniao = $this->criarReuniao();

        ReuniaoFeedback::create([
            'gestor_id'            => $reuniao->gestor_id,
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Texto de exemplo',
            'resumo_ia'            => 'Resumo gerado pela IA.',
        ]);

        $reuniao->refresh();
        $this->assertInstanceOf(ReuniaoFeedback::class, $reuniao->feedback);
    }

    // =========================================================================
    // Transições de status (regras de domínio)
    // =========================================================================

    #[Test]
    public function status_inicial_deve_ser_agendado(): void
    {
        $reuniao = $this->criarReuniao(['status' => 'agendado']);

        $this->assertEquals('agendado', $reuniao->status);
    }

    #[Test]
    public function pode_atualizar_status_para_em_andamento(): void
    {
        $reuniao = $this->criarReuniao();
        $reuniao->update(['status' => 'em_andamento']);

        $this->assertEquals('em_andamento', $reuniao->fresh()->status);
    }

    #[Test]
    public function pode_atualizar_status_para_concluido(): void
    {
        $reuniao = $this->criarReuniao();
        $reuniao->update(['status' => 'concluido']);

        $this->assertEquals('concluido', $reuniao->fresh()->status);
    }

    // =========================================================================
    // Persistência no banco
    // =========================================================================

    #[Test]
    public function persiste_reuniao_no_banco_com_campos_corretos(): void
    {
        $reuniao = $this->criarReuniao(['titulo' => 'Reunião de Planejamento']);

        $this->assertDatabaseHas('reuniao_agendamentos', [
            'id'     => $reuniao->id,
            'titulo' => 'Reunião de Planejamento',
            'status' => 'agendado',
        ]);
    }

    #[Test]
    public function exclui_reuniao_do_banco(): void
    {
        $reuniao = $this->criarReuniao();
        $id      = $reuniao->id;

        $reuniao->delete();

        $this->assertDatabaseMissing('reuniao_agendamentos', ['id' => $id]);
    }
}
