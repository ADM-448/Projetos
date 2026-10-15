<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Atividade;
use App\Enums\StatusAvaliacao;
use App\Enums\UserRole;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;

class NotificacaoDiariaPendenciasTest extends TestCase
{
    use RefreshDatabase;

    public function test_apenas_quem_nao_respondeu_recebe_notificacao_diaria()
    {
        Mail::fake();

        $empresa = Empresa::factory()->create();

        $colaborador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'email' => 'colaborador@teste.com',
            'telefone' => '11999999991',
        ]);

        $gestor = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::ADMIN->value,
            'email' => 'gestor@teste.com',
            'telefone' => '11999999992',
        ]);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Avaliação de Desempenho 2026',
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $gestor->id,
        ]);

        // Participação 1: Autoavaliação do colaborador (JÁ RESPONDIDA)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $colaborador->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'autoavaliacao',
            'status' => 'respondido',
        ]);

        // Atividade que tinha ficado não lida no banco para o colaborador
        $ativColab = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $colaborador->id,
            'titulo' => 'Avaliação de Desempenho 2026',
            'descricao' => 'Você tem uma autoavaliação pendente',
            'url' => route('avaliacoes.minhas'),
            'lida' => false,
        ]);

        // Participação 2: Avaliação do gestor sobre o colaborador (AINDA PENDENTE)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $gestor->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'gestor',
            'status' => 'pendente',
        ]);

        // Atividade não lida para o gestor
        $ativGestor = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $gestor->id,
            'titulo' => 'Avaliação de Desempenho 2026',
            'descricao' => 'Você foi designado para avaliar o colaborador',
            'url' => route('avaliacoes.show', $avaliacao->id),
            'lida' => false,
        ]);

        // Mock do WhatsAppService
        $mockWhatsApp = Mockery::mock(WhatsAppService::class);
        $mockWhatsApp->shouldReceive('setEmpresaId')->with($empresa->id)->andReturnSelf();
        
        // Esperamos que o WhatsApp seja chamado APENAS para o Gestor, NUNCA para o colaborador!
        $mockWhatsApp->shouldReceive('notificar')
            ->once()
            ->withArgs(function ($user, $msg) use ($gestor) {
                return $user->id === $gestor->id;
            });

        $this->app->instance(WhatsAppService::class, $mockWhatsApp);

        // Executa o comando diário
        $this->artisan('notify:daily')
            ->assertSuccessful();

        // 1. A atividade do colaborador que já tinha respondido deve ter sido saneada (lida = true)
        $ativColab->refresh();
        $this->assertTrue((bool)$ativColab->lida);

        // 2. A atividade do gestor que ainda não respondeu continua pendente
        $ativGestor->refresh();
        $this->assertFalse((bool)$ativGestor->lida);

        // 3. E-mail foi enviado somente para o gestor
        Mail::assertSent(\App\Mail\AtividadesPendentesMail::class, function ($mail) use ($gestor) {
            return $mail->hasTo($gestor->email);
        });

        Mail::assertNotSent(\App\Mail\AtividadesPendentesMail::class, function ($mail) use ($colaborador) {
            return $mail->hasTo($colaborador->email);
        });
    }

    public function test_ao_responder_avaliacao_marca_atividade_como_lida()
    {
        $empresa = Empresa::factory()->create();

        $colaborador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR->value,
        ]);

        $this->actingAs($colaborador);

        $competencia = new \App\Models\Competencia();
        $competencia->nome = 'Comunicação';
        $competencia->empresa_id = $empresa->id;
        $competencia->save();

        $skill = \App\Models\Skill::create(['competencia_id' => $competencia->id, 'nome' => 'Clareza']);

        $avaliacao = Avaliacao::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Campanha Semestral',
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $colaborador->id,
        ]);
        $avaliacao->competencias()->attach($competencia->id);

        $participante = AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliador_id' => $colaborador->id,
            'avaliado_id' => $colaborador->id,
            'tipo' => 'autoavaliacao',
            'status' => 'pendente',
        ]);

        $atividade = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $colaborador->id,
            'titulo' => 'Campanha Semestral',
            'descricao' => 'Você tem uma autoavaliação pendente',
            'url' => route('avaliacoes.minhas'),
            'lida' => false,
        ]);

        $this->actingAs($colaborador)
            ->post(route('avaliacoes.store-resposta', $participante), [
                'respostas' => [$skill->id => 5],
                'observacao' => 'Concluído com sucesso',
            ])
            ->assertRedirect(route('avaliacoes.minhas'));

        $participante->refresh();
        $this->assertEquals('respondido', $participante->status);

        $atividade->refresh();
        $this->assertTrue((bool)$atividade->lida, 'A atividade do avaliador deve ser marcada como lida após conclusão.');
    }

    public function test_reuniao_concluida_nao_gera_notificacao_diaria_e_saneia_atividade()
    {
        Mail::fake();

        $empresa = Empresa::factory()->create();

        $colaborador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR->value,
            'email' => 'colab_reuniao@teste.com',
            'telefone' => '11999999993',
        ]);

        $gestor = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::ADMIN->value,
        ]);

        // Reunião 1: Já foi realizada ontem e está concluída
        $reuniaoConcluida = \App\Models\ReuniaoAgendamento::create([
            'gestor_id' => $gestor->id,
            'titulo' => 'Feedback 1:1 Concluído',
            'data_hora' => now()->subDay(),
            'status' => 'concluido',
        ]);

        $ativConcluida = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $colaborador->id,
            'titulo' => $reuniaoConcluida->titulo,
            'descricao' => 'Você foi convidado para a reunião.',
            'url' => route('reunioes.sala', $reuniaoConcluida->id),
            'lida' => false,
        ]);

        // Mock do WhatsAppService
        $mockWhatsApp = Mockery::mock(WhatsAppService::class);
        $mockWhatsApp->shouldReceive('setEmpresaId')->with($empresa->id)->andReturnSelf();
        // Não deve disparar nenhuma notificação no WhatsApp
        $mockWhatsApp->shouldNotReceive('notificar');

        $this->app->instance(WhatsAppService::class, $mockWhatsApp);

        // Executa o comando diário
        $this->artisan('notify:daily')->assertSuccessful();

        // 1. A atividade deve ter sido saneada automaticamente para lida = true
        $ativConcluida->refresh();
        $this->assertTrue((bool)$ativConcluida->lida, 'A atividade da reunião concluída deve ser saneada como lida.');

        // 2. Nenhum e-mail deve ter sido enviado
        Mail::assertNothingSent();
    }
}
