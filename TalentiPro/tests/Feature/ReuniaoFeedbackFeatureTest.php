<?php

namespace Tests\Feature;

use App\Enums\StatusPdi;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\ReuniaoAgendamento;
use App\Models\ReuniaoFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testes de integração para o ReuniaoFeedbackController.
 *
 * Cobrem todas as rotas do módulo de reuniões:
 *   - index, create, store
 *   - sala (autorização por participante)
 *   - salvarManual
 *   - show (visualização e redirecionamento)
 *   - destroy (exclusão com limpeza de storage)
 *   - updateRegistro (edição inline de resumo e PDIs)
 *
 * Cada cenário testa tanto o caminho feliz (happy path) quanto as
 * principais proteções de segurança (403 para não autorizados).
 */
class ReuniaoFeedbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Cria uma empresa com IA ativa e um par gestor/colaborador associados.
     */
    private function criarEmpresaComUsuarios(array $empresaExtra = []): array
    {
        $empresa = Empresa::factory()->create(array_merge([
            'ia_ativa'                    => true,
            'ia_limite_transcricoes_mes'  => 10,
            'ia_transcricoes_utilizadas'  => 0,
        ], $empresaExtra));

        $gestor = User::factory()->create([
            'empresa_id'    => $empresa->id,
            'perfil_acesso' => 'gestor',
        ]);

        $colaborador = User::factory()->create([
            'empresa_id'    => $empresa->id,
            'perfil_acesso' => 'colaborador',
        ]);

        return compact('empresa', 'gestor', 'colaborador');
    }

    private function criarReuniao(User $gestor, User $colaborador, array $extra = []): ReuniaoAgendamento
    {
        $reuniao = ReuniaoAgendamento::create(array_merge([
            'gestor_id'      => $gestor->id,
            'colaborador_id' => $colaborador->id,
            'titulo'         => 'Reunião de Feedback 1:1',
            'data_hora'      => now()->addDay(),
            'status'         => 'agendado',
        ], $extra));

        $reuniao->participantes()->sync([$colaborador->id]);

        return $reuniao;
    }

    /**
     * Cria um feedback (relatório) completo com PDIs vinculados.
     */
    private function criarFeedbackComPdis(ReuniaoAgendamento $reuniao, User $gestor, User $colaborador, Empresa $empresa, int $numeroPdis = 2): ReuniaoFeedback
    {
        $feedback = ReuniaoFeedback::create([
            'gestor_id'            => $gestor->id,
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Transcrição completa da reunião de feedback.',
            'resumo_ia'            => 'Resumo executivo gerado automaticamente pela IA.',
            'sentimento_ia'        => 'Positivo',
            'audio_path'           => null,
        ]);

        $feedback->participantes()->sync([$gestor->id, $colaborador->id]);

        for ($i = 1; $i <= $numeroPdis; $i++) {
            Pdi::create([
                'empresa_id'          => $empresa->id,
                'colaborador_id'      => $colaborador->id,
                'gestor_id'           => $gestor->id,
                'reuniao_feedback_id' => $feedback->id,
                'titulo'              => "PDI #{$i} da Reunião",
                'descricao'           => "Descrição detalhada do PDI #{$i}.",
                'data_fim'            => now()->addDays(30)->format('Y-m-d'),
                'status'              => StatusPdi::NAO_INICIADO,
                'progresso'           => 0,
            ]);
        }

        return $feedback;
    }

    // =========================================================================
    // index
    // =========================================================================

    #[Test]
    public function gestor_lista_somente_suas_proprias_reunioes(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();

        // Reunião do gestor
        $reuniaoDoGestor = $this->criarReuniao($gestor, $colaborador, ['titulo' => 'Minha Reunião']);

        // Reunião de outro gestor na mesma empresa
        $outroGestor = User::factory()->create(['empresa_id' => $gestor->empresa_id, 'perfil_acesso' => 'gestor']);
        $this->criarReuniao($outroGestor, $colaborador, ['titulo' => 'Reunião Alheia']);

        $response = $this->actingAs($gestor)->get(route('reunioes.index'));

        $response->assertStatus(200);
        $response->assertSee('Minha Reunião');
        $response->assertDontSee('Reunião Alheia');
    }

    #[Test]
    public function admin_lista_todas_as_reunioes_da_empresa(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $admin = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'admin']);

        $gestorA = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'gestor']);
        $gestorB = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'gestor']);

        $this->criarReuniao($gestorA, $colaborador, ['titulo' => 'Reunião do Gestor A']);
        $this->criarReuniao($gestorB, $colaborador, ['titulo' => 'Reunião do Gestor B']);

        $response = $this->actingAs($admin)->get(route('reunioes.index'));

        $response->assertStatus(200);
        $response->assertSee('Reunião do Gestor A');
        $response->assertSee('Reunião do Gestor B');
    }

    #[Test]
    public function usuario_nao_autenticado_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('reunioes.index'));

        $response->assertRedirect(route('login'));
    }

    // =========================================================================
    // create / store
    // =========================================================================

    #[Test]
    public function gestor_pode_acessar_formulario_de_criacao(): void
    {
        ['gestor' => $gestor] = $this->criarEmpresaComUsuarios();

        $response = $this->actingAs($gestor)->get(route('reunioes.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function gestor_cria_reuniao_com_dados_validos(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();

        $dataHora = now()->addDays(2)->format('Y-m-d\TH:i');

        $response = $this->actingAs($gestor)->post(route('reunioes.store'), [
            'titulo'         => 'Reunião de Avaliação Trimestral',
            'participantes'  => [$colaborador->id],
            'tipo_reuniao'   => 'local',
            'data_hora'      => $dataHora,
        ]);

        $response->assertRedirect(route('reunioes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reuniao_agendamentos', [
            'titulo'         => 'Reunião de Avaliação Trimestral',
            'gestor_id'      => $gestor->id,
            'status'         => 'agendado',
        ]);
    }

    #[Test]
    public function store_valida_titulo_obrigatorio(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();

        $response = $this->actingAs($gestor)->post(route('reunioes.store'), [
            'titulo'         => '',
            'participantes'  => [$colaborador->id],
            'tipo_reuniao'   => 'local',
            'data_hora'      => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors(['titulo']);
    }

    #[Test]
    public function store_valida_data_hora_no_passado(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();

        $response = $this->actingAs($gestor)->post(route('reunioes.store'), [
            'titulo'         => 'Reunião Inválida',
            'participantes'  => [$colaborador->id],
            'tipo_reuniao'   => 'local',
            'data_hora'      => now()->subDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors(['data_hora']);
    }

    #[Test]
    public function store_valida_colaborador_existente(): void
    {
        ['gestor' => $gestor] = $this->criarEmpresaComUsuarios();

        $response = $this->actingAs($gestor)->post(route('reunioes.store'), [
            'titulo'         => 'Reunião Test',
            'participantes'  => [99999], // ID inválido
            'tipo_reuniao'   => 'local',
            'data_hora'      => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors(['participantes.0']);
    }

    // =========================================================================
    // sala
    // =========================================================================

    #[Test]
    public function gestor_acessa_sala_da_propria_reuniao(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->get(route('reunioes.sala', $reuniao));

        $response->assertStatus(200);
    }

    #[Test]
    public function colaborador_acessa_sala_da_propria_reuniao(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($colaborador)->get(route('reunioes.sala', $reuniao));

        $response->assertStatus(200);
    }

    #[Test]
    public function usuario_nao_participante_nao_acessa_sala(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador, 'empresa' => $empresa] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $intruso = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'colaborador']);

        $response = $this->actingAs($intruso)->get(route('reunioes.sala', $reuniao));

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_acessa_sala_de_qualquer_reuniao_da_empresa(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $admin   = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'admin']);
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($admin)->get(route('reunioes.sala', $reuniao));

        $response->assertStatus(200);
    }

    // =========================================================================
    // salvarManual
    // =========================================================================

    #[Test]
    public function gestor_salva_relatorio_manual_com_sucesso(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->post(route('reunioes.salvar_manual', $reuniao), [
            'resumo' => 'Esta é uma descrição detalhada do que foi discutido na reunião de feedback.',
        ]);

        $response->assertRedirect(route('reunioes.show', $reuniao->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reuniao_feedbacks', [
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Registro Manual',
        ]);

        $this->assertEquals('concluido', $reuniao->fresh()->status);
    }

    #[Test]
    public function salvar_manual_valida_resumo_obrigatorio(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->post(route('reunioes.salvar_manual', $reuniao), [
            'resumo' => '',
        ]);

        $response->assertSessionHasErrors(['resumo']);
    }

    #[Test]
    public function salvar_manual_valida_resumo_minimo_de_10_caracteres(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->post(route('reunioes.salvar_manual', $reuniao), [
            'resumo' => 'Curto',
        ]);

        $response->assertSessionHasErrors(['resumo']);
    }

    #[Test]
    public function colaborador_nao_pode_salvar_relatorio_manual(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($colaborador)->post(route('reunioes.salvar_manual', $reuniao), [
            'resumo' => 'Tentativa de salvar pelo colaborador.',
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // show
    // =========================================================================

    #[Test]
    public function gestor_visualiza_relatorio_concluido(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($gestor)->get(route('reunioes.show', $reuniao));

        $response->assertStatus(200);
        $response->assertSee('Resumo Executivo');
        $response->assertSee('PDI #1');
    }

    #[Test]
    public function colaborador_visualiza_relatorio_da_propria_reuniao(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($colaborador)->get(route('reunioes.show', $reuniao));

        $response->assertStatus(200);
    }

    #[Test]
    public function show_redireciona_quando_feedback_nao_existe(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->get(route('reunioes.show', $reuniao));

        $response->assertRedirect(route('reunioes.index'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function intruso_nao_visualiza_relatorio_de_reuniao_alheia(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $intruso  = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'colaborador']);

        $response = $this->actingAs($intruso)->get(route('reunioes.show', $reuniao));

        $response->assertStatus(403);
    }

    // =========================================================================
    // destroy
    // =========================================================================

    #[Test]
    public function gestor_exclui_reuniao_com_feedback_e_pdis(): void
    {
        Storage::fake('local');

        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $reuniaoId  = $reuniao->id;
        $feedbackId = $feedback->id;

        $response = $this->actingAs($gestor)->delete(route('reunioes.destroy', $reuniao));

        $response->assertRedirect(route('reunioes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reuniao_agendamentos', ['id' => $reuniaoId]);
        $this->assertDatabaseMissing('reuniao_feedbacks', ['id' => $feedbackId]);
        $this->assertDatabaseMissing('pdis', ['reuniao_feedback_id' => $feedbackId]);
    }

    #[Test]
    public function gestor_exclui_reuniao_sem_feedback(): void
    {
        ['gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador);
        $reuniaoId = $reuniao->id;

        $response = $this->actingAs($gestor)->delete(route('reunioes.destroy', $reuniao));

        $response->assertRedirect(route('reunioes.index'));
        $this->assertDatabaseMissing('reuniao_agendamentos', ['id' => $reuniaoId]);
    }

    #[Test]
    public function destroy_exclui_arquivo_de_audio_do_storage(): void
    {
        Storage::fake();

        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);

        // Simula um áudio no storage
        $audioPath = 'reunioes/audios/reuniao_1_teste.webm';
        Storage::put($audioPath, 'fake audio content');

        $feedback = ReuniaoFeedback::create([
            'gestor_id'            => $gestor->id,
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Texto.',
            'resumo_ia'            => 'Resumo da reunião com áudio gravado.',
            'audio_path'           => $audioPath,
        ]);

        Storage::assertExists($audioPath);

        $this->actingAs($gestor)->delete(route('reunioes.destroy', $reuniao));

        Storage::assertMissing($audioPath);
    }

    #[Test]
    public function colaborador_nao_pode_excluir_reuniao(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($colaborador)->delete(route('reunioes.destroy', $reuniao));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reuniao_agendamentos', ['id' => $reuniao->id]);
    }

    #[Test]
    public function admin_pode_excluir_qualquer_reuniao_da_empresa(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $admin   = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => 'admin']);
        $reuniao = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($admin)->delete(route('reunioes.destroy', $reuniao));

        $response->assertRedirect(route('reunioes.index'));
        $this->assertDatabaseMissing('reuniao_agendamentos', ['id' => $reuniao->id]);
    }

    // =========================================================================
    // ETAPA 1 DA IA: Upload e Transcrição do Áudio
    // =========================================================================

    #[Test]
    public function test_reuniao_bloqueia_ia_se_ia_inativa(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $empresa->update(['ia_ativa' => false]);

        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.upload_audio', $reuniao),
            ['audio' => \Illuminate\Http\UploadedFile::fake()->create('audio.webm', 100, 'audio/webm')]
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => false,
            'error' => 'Limite de IA excedido ou serviço desabilitado.'
        ]);
    }

    #[Test]
    public function test_reuniao_bloqueia_ia_se_limite_atingido(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $empresa->update([
            'ia_ativa' => true,
            'ia_limite_transcricoes_mes' => 5,
            'ia_transcricoes_utilizadas' => 5,
        ]);

        $reuniao = $this->criarReuniao($gestor, $colaborador);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.upload_audio', $reuniao),
            ['audio' => \Illuminate\Http\UploadedFile::fake()->create('audio.webm', 100, 'audio/webm')]
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => false,
            'error' => 'Limite de IA excedido ou serviço desabilitado.'
        ]);
    }

    // =========================================================================
    // updateRegistro
    // =========================================================================

    #[Test]
    public function gestor_atualiza_resumo_do_relatorio(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $novoResumo = 'Resumo atualizado pelo gestor após revisão completa do registro de reunião.';

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            ['resumo' => $novoResumo]
        );

        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('reuniao_feedbacks', [
            'id'        => $feedback->id,
            'resumo_ia' => $novoResumo,
        ]);
    }

    #[Test]
    public function gestor_adiciona_novo_pdi_via_update_registro(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa, 0);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            [
                'resumo' => 'Resumo revisado com novo PDI adicionado pelo gestor.',
                'pdis'   => [
                    [
                        'colaborador_id' => $colaborador->id,
                        'titulo'         => 'Novo PDI via Edição',
                        'descricao'      => 'Descrição do PDI adicionado durante a edição.',
                        'prazo'          => now()->addDays(15)->format('Y-m-d'),
                    ],
                ],
            ]
        );

        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('pdis', [
            'reuniao_feedback_id' => $feedback->id,
            'titulo'              => 'Novo PDI via Edição',
            'colaborador_id'      => $colaborador->id,
        ]);
    }

    #[Test]
    public function gestor_atualiza_pdi_existente_via_update_registro(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa, 1);

        $pdi = $feedback->pdis->first();

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            [
                'resumo' => 'Resumo após atualização de PDI existente pelo gestor.',
                'pdis'   => [
                    [
                        'id'             => $pdi->id,
                        'colaborador_id' => $colaborador->id,
                        'titulo'         => 'Título Atualizado do PDI',
                        'descricao'      => 'Descrição atualizada com novos detalhes.',
                        'prazo'          => now()->addDays(45)->format('Y-m-d'),
                    ],
                ],
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('pdis', [
            'id'     => $pdi->id,
            'titulo' => 'Título Atualizado do PDI',
        ]);
    }

    #[Test]
    public function gestor_adiciona_novo_pdi_com_acoes_via_update_registro(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa, 0);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            [
                'resumo' => 'Resumo com PDI e ações.',
                'pdis'   => [
                    [
                        'colaborador_id' => $colaborador->id,
                        'titulo'         => 'PDI com Ações',
                        'descricao'      => 'PDI que possui etapas.',
                        'acoes'          => [
                            [
                                'titulo'    => 'Ação 1',
                                'descricao' => 'Descrição da ação 1',
                            ],
                            [
                                'titulo'    => 'Ação 2',
                            ]
                        ]
                    ],
                ],
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('pdis', [
            'reuniao_feedback_id' => $feedback->id,
            'titulo'              => 'PDI com Ações',
        ]);

        $pdi = Pdi::where('titulo', 'PDI com Ações')->first();

        $this->assertDatabaseHas('pdi_acoes', [
            'pdi_id' => $pdi->id,
            'titulo' => 'Ação 1',
        ]);
        
        $this->assertDatabaseHas('pdi_acoes', [
            'pdi_id' => $pdi->id,
            'titulo' => 'Ação 2',
        ]);
    }

    #[Test]
    public function gestor_atualiza_pdi_existente_e_suas_acoes_via_update_registro(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa, 1);

        $pdi = $feedback->pdis->first();
        
        // Criar uma ação existente para o PDI
        $acaoExistente = $pdi->acoes()->create([
            'titulo' => 'Ação Antiga',
            'status' => 'pendente'
        ]);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            [
                'resumo' => 'Atualizando PDI e ações.',
                'pdis'   => [
                    [
                        'id'             => $pdi->id,
                        'colaborador_id' => $colaborador->id,
                        'titulo'         => 'PDI Editado',
                        'acoes'          => [
                            [
                                'id'        => $acaoExistente->id,
                                'titulo'    => 'Ação Antiga Editada',
                            ],
                            [
                                'titulo'    => 'Ação Nova',
                            ]
                        ]
                    ],
                ],
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('pdi_acoes', [
            'id'     => $acaoExistente->id,
            'titulo' => 'Ação Antiga Editada',
        ]);

        $this->assertDatabaseHas('pdi_acoes', [
            'pdi_id' => $pdi->id,
            'titulo' => 'Ação Nova',
        ]);
    }

    #[Test]
    public function gestor_deleta_pdi_via_update_registro(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa, 2);

        $pdis       = $feedback->pdis;
        $pdiParaDeletar = $pdis->first();

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            [
                'resumo'       => 'Resumo após remoção de PDI pelo gestor.',
                'deletar_pdis' => [$pdiParaDeletar->id],
            ]
        );

        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('pdis', ['id' => $pdiParaDeletar->id]);
        // O segundo PDI deve continuar existindo
        $this->assertDatabaseHas('pdis', ['id' => $pdis->last()->id]);
    }

    #[Test]
    public function update_registro_valida_resumo_obrigatorio(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            ['resumo' => '']
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['resumo']);
    }

    #[Test]
    public function colaborador_nao_pode_editar_relatorio(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $response = $this->actingAs($colaborador)->postJson(
            route('reunioes.update_registro', $reuniao),
            ['resumo' => 'Tentativa de edição pelo colaborador.']
        );

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => false,
            'error' => 'Apenas o criador da reunião pode editar este registro.'
        ]);
    }

    #[Test]
    public function update_registro_impede_deletar_pdi_de_outra_reuniao(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();

        // Reunião e feedback A
        $reuniaoA  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $this->criarFeedbackComPdis($reuniaoA, $gestor, $colaborador, $empresa, 1);

        // Reunião e feedback B com PDI alvo
        $reuniaoB  = $this->criarReuniao($gestor, $colaborador, ['titulo' => 'Reunião B', 'status' => 'concluido']);
        $feedbackB = $this->criarFeedbackComPdis($reuniaoB, $gestor, $colaborador, $empresa, 1);
        $pdiAlheio = $feedbackB->pdis->first();

        // Tenta deletar o PDI da reunião B ao editar a reunião A
        $response = $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniaoA),
            [
                'resumo'       => 'Tentativa de deletar PDI de outra reunião.',
                'deletar_pdis' => [$pdiAlheio->id],
            ]
        );

        // O controller filtra pela reuniao_feedback_id, então o PDI alheio deve permanecer
        $response->assertStatus(200);
        $this->assertDatabaseHas('pdis', ['id' => $pdiAlheio->id]);
    }

    #[Test]
    public function update_registro_atualiza_timestamp_do_feedback(): void
    {
        ['empresa' => $empresa, 'gestor' => $gestor, 'colaborador' => $colaborador] = $this->criarEmpresaComUsuarios();
        $reuniao  = $this->criarReuniao($gestor, $colaborador, ['status' => 'concluido']);
        $feedback = $this->criarFeedbackComPdis($reuniao, $gestor, $colaborador, $empresa);

        $updatedAtAntes = $feedback->updated_at;

        sleep(1);

        $this->actingAs($gestor)->postJson(
            route('reunioes.update_registro', $reuniao),
            ['resumo' => 'Resumo atualizado para validar o timestamp de modificação.']
        );

        $this->assertTrue($feedback->fresh()->updated_at->gt($updatedAtAntes));
    }
}
