<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\ReuniaoAgendamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cobre a gravação em segundo plano: popup gravador, estado/heartbeat,
 * widget global, rascunho persistido e cancelamento.
 */
class ReuniaoGravacaoSegundoPlanoTest extends TestCase
{
    use RefreshDatabase;

    private function cenario(array $empresaExtra = []): array
    {
        $empresa = Empresa::factory()->create(array_merge([
            'ia_ativa'                   => true,
            'ia_limite_transcricoes_mes' => 10,
            'ia_transcricoes_utilizadas' => 0,
        ], $empresaExtra));

        $gestor = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'gestor']);
        $colab  = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'colaborador']);

        $reuniao = ReuniaoAgendamento::create([
            'gestor_id'      => $gestor->id,
            'colaborador_id' => $colab->id,
            'titulo'         => 'Feedback 1:1',
            'data_hora'      => now()->addDay(),
            'status'         => 'agendado',
        ]);
        $reuniao->participantes()->sync([$colab->id]);

        return compact('empresa', 'gestor', 'colab', 'reuniao');
    }

    // ---------------------------------------------------------------- gravador

    #[Test]
    public function gestor_acessa_a_tela_do_popup_gravador(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($gestor)
            ->get(route('reunioes.gravador', $reuniao->id))
            ->assertOk()
            ->assertSee('Feedback 1:1');
    }

    #[Test]
    public function colaborador_nao_acessa_o_gravador(): void
    {
        ['colab' => $colab, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($colab)
            ->get(route('reunioes.gravador', $reuniao->id))
            ->assertForbidden();
    }

    #[Test]
    public function gravador_redireciona_para_a_sala_quando_ia_indisponivel(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario(['ia_ativa' => false]);

        $this->actingAs($gestor)
            ->get(route('reunioes.gravador', $reuniao->id))
            ->assertRedirect(route('reunioes.sala', $reuniao->id));
    }

    // ------------------------------------------------------ iniciar / heartbeat

    #[Test]
    public function iniciar_gravacao_marca_status_gravando(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($gestor)
            ->postJson(route('reunioes.gravacao.iniciar', $reuniao->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $reuniao->refresh();
        $this->assertSame('gravando', $reuniao->gravacao_status);
        $this->assertSame('em_andamento', $reuniao->status);
        $this->assertNotNull($reuniao->gravacao_iniciada_em);
        $this->assertNotNull($reuniao->gravacao_heartbeat_em);
    }

    #[Test]
    public function colaborador_nao_pode_iniciar_gravacao(): void
    {
        ['colab' => $colab, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($colab)
            ->postJson(route('reunioes.gravacao.iniciar', $reuniao->id))
            ->assertForbidden();

        $this->assertSame('nenhuma', $reuniao->fresh()->gravacao_status);
    }

    #[Test]
    public function heartbeat_atualiza_o_timestamp_enquanto_grava(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update(['gravacao_status' => 'gravando', 'gravacao_heartbeat_em' => now()->subSeconds(10)]);

        $this->actingAs($gestor)
            ->postJson(route('reunioes.gravacao.heartbeat', $reuniao->id))
            ->assertOk()
            ->assertJson(['status' => 'gravando']);

        $this->assertTrue($reuniao->fresh()->gravacao_heartbeat_em->diffInSeconds(now()) < 5);
    }

    #[Test]
    public function heartbeat_nao_altera_quando_nao_esta_gravando(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($gestor)
            ->postJson(route('reunioes.gravacao.heartbeat', $reuniao->id))
            ->assertOk()
            ->assertJson(['status' => 'nenhuma']);

        $this->assertNull($reuniao->fresh()->gravacao_heartbeat_em);
    }

    // -------------------------------------------------------- status / widget

    #[Test]
    public function gravacao_ativa_retorna_false_sem_gravacao(): void
    {
        ['gestor' => $gestor] = $this->cenario();

        $this->actingAs($gestor)
            ->getJson(route('reunioes.gravacao.ativa'))
            ->assertOk()
            ->assertExactJson(['ativa' => false]);
    }

    #[Test]
    public function gravacao_ativa_retorna_dados_para_o_widget(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update([
            'gravacao_status'       => 'gravando',
            'gravacao_iniciada_em'  => now()->subSeconds(90),
            'gravacao_heartbeat_em' => now(),
        ]);

        $this->actingAs($gestor)
            ->getJson(route('reunioes.gravacao.ativa'))
            ->assertOk()
            ->assertJson([
                'ativa'        => true,
                'reuniao_id'   => $reuniao->id,
                'status'       => 'gravando',
                'interrompida' => false,
            ])
            ->assertJsonPath('url_sala', route('reunioes.sala', $reuniao->id) . '?etapa=transcricao')
            ->assertJsonPath('url_gravador', route('reunioes.gravador', $reuniao->id));
    }

    #[Test]
    public function gravacao_sem_heartbeat_e_marcada_como_interrompida(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update([
            'gravacao_status'       => 'gravando',
            'gravacao_iniciada_em'  => now()->subMinutes(5),
            'gravacao_heartbeat_em' => now()->subSeconds(ReuniaoAgendamento::HEARTBEAT_TIMEOUT + 5),
        ]);

        $this->actingAs($gestor)
            ->getJson(route('reunioes.gravacao.ativa'))
            ->assertJson(['ativa' => true, 'interrompida' => true]);
    }

    #[Test]
    public function gravacao_ativa_ignora_gravacoes_de_outros_gestores(): void
    {
        ['empresa' => $empresa, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update(['gravacao_status' => 'gravando', 'gravacao_iniciada_em' => now()]);

        $outro = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'gestor']);

        $this->actingAs($outro)
            ->getJson(route('reunioes.gravacao.ativa'))
            ->assertExactJson(['ativa' => false]);
    }

    #[Test]
    public function gravacao_transcrita_continua_visivel_para_revisao(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update(['gravacao_status' => 'transcrita', 'gravacao_iniciada_em' => now(), 'transcricao_rascunho' => 'Texto']);

        $this->actingAs($gestor)
            ->getJson(route('reunioes.gravacao.ativa'))
            ->assertJson(['ativa' => true, 'status' => 'transcrita']);
    }

    #[Test]
    public function status_gravacao_so_para_o_organizador(): void
    {
        ['gestor' => $gestor, 'colab' => $colab, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($gestor)->getJson(route('reunioes.gravacao.status', $reuniao->id))->assertOk();
        $this->actingAs($colab)->getJson(route('reunioes.gravacao.status', $reuniao->id))->assertForbidden();
    }

    // -------------------------------------------------------------- rascunho

    #[Test]
    public function salvar_rascunho_persiste_transcricao_e_analise(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($gestor)
            ->postJson(route('reunioes.rascunho', $reuniao->id), [
                'transcricao' => 'Transcrição editada',
                'analise'     => ['resumo' => 'Resumo X', 'sentimento' => 'Neutro', 'pdis' => []],
            ])
            ->assertOk();

        $reuniao->refresh();
        $this->assertSame('Transcrição editada', $reuniao->transcricao_rascunho);
        $this->assertSame('Resumo X', $reuniao->analise_rascunho['resumo']);
    }

    #[Test]
    public function salvar_rascunho_negado_para_colaborador(): void
    {
        ['colab' => $colab, 'reuniao' => $reuniao] = $this->cenario();

        $this->actingAs($colab)
            ->postJson(route('reunioes.rascunho', $reuniao->id), ['transcricao' => 'x'])
            ->assertForbidden();
    }

    #[Test]
    public function sala_retoma_rascunho_da_transcricao_salvo(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update(['gravacao_status' => 'transcrita', 'transcricao_rascunho' => 'Trecho recuperado do rascunho']);

        $this->actingAs($gestor)
            ->get(route('reunioes.sala', $reuniao->id))
            ->assertOk()
            ->assertSee('Trecho recuperado do rascunho', false);
    }

    // -------------------------------------------------------------- cancelar

    #[Test]
    public function cancelar_gravacao_limpa_o_rascunho_e_volta_status(): void
    {
        ['gestor' => $gestor, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update([
            'status'               => 'em_andamento',
            'gravacao_status'      => 'gravando',
            'transcricao_rascunho' => 'abc',
        ]);

        $this->actingAs($gestor)
            ->postJson(route('reunioes.gravacao.cancelar', $reuniao->id))
            ->assertOk();

        $reuniao->refresh();
        $this->assertSame('nenhuma', $reuniao->gravacao_status);
        $this->assertSame('agendado', $reuniao->status);
        $this->assertNull($reuniao->transcricao_rascunho);
    }

    #[Test]
    public function colaborador_nao_pode_cancelar_gravacao(): void
    {
        ['colab' => $colab, 'reuniao' => $reuniao] = $this->cenario();
        $reuniao->update(['gravacao_status' => 'gravando']);

        $this->actingAs($colab)
            ->postJson(route('reunioes.gravacao.cancelar', $reuniao->id))
            ->assertForbidden();

        $this->assertSame('gravando', $reuniao->fresh()->gravacao_status);
    }

    // ----------------------------------------------------------- unit: model

    #[Test]
    public function model_limpar_rascunho_zera_todos_os_campos(): void
    {
        ['reuniao' => $reuniao] = $this->cenario();
        $reuniao->update([
            'gravacao_status'       => 'analisada',
            'gravacao_iniciada_em'  => now(),
            'gravacao_heartbeat_em' => now(),
            'audio_path'            => 'reunioes/audios/x.webm',
            'transcricao_rascunho'  => 'txt',
            'analise_rascunho'      => ['resumo' => 'r'],
        ]);

        $reuniao->limparRascunho();
        $reuniao->refresh();

        $this->assertSame('nenhuma', $reuniao->gravacao_status);
        $this->assertNull($reuniao->audio_path);
        $this->assertNull($reuniao->transcricao_rascunho);
        $this->assertNull($reuniao->analise_rascunho);
        $this->assertNull($reuniao->gravacao_iniciada_em);
    }

    #[Test]
    public function model_gravacao_interrompida_respeita_o_timeout(): void
    {
        ['reuniao' => $reuniao] = $this->cenario();

        $reuniao->gravacao_status = 'gravando';
        $reuniao->gravacao_heartbeat_em = now()->subSeconds(ReuniaoAgendamento::HEARTBEAT_TIMEOUT - 5);
        $this->assertFalse($reuniao->gravacaoInterrompida());

        $reuniao->gravacao_heartbeat_em = now()->subSeconds(ReuniaoAgendamento::HEARTBEAT_TIMEOUT + 5);
        $this->assertTrue($reuniao->gravacaoInterrompida());

        $reuniao->gravacao_status = 'transcrita';
        $this->assertFalse($reuniao->gravacaoInterrompida());
    }
}
