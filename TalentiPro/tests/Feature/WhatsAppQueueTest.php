<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Models\WhatsappQueue;
use App\Jobs\ProcessarFilaWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Suite: WhatsAppQueueTest
 * Cobre: Fila de mensagens, modelo WhatsappQueue, reprocessamento e autorização.
 */
class WhatsAppQueueTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $gestor;
    private User $colaborador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create([
            'status' => StatusEmpresa::ATIVA,
            'whatsapp_ativo' => true,
        ]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->gestor = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Scope de pendentes
    // ──────────────────────────────────────────────────────────────────────────

    public function test_scope_pendentes_retorna_apenas_com_tentativas_disponiveis(): void
    {
        // Item pendente válido
        WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511999999999',
            'mensagem'       => 'Teste',
            'status'         => 'pendente',
            'tentativas'     => 1,
            'max_tentativas' => 3,
        ]);

        // Item que esgotou tentativas (não deve aparecer no scope)
        WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511888888888',
            'mensagem'       => 'Exausto',
            'status'         => 'pendente',
            'tentativas'     => 3,
            'max_tentativas' => 3,
        ]);

        $pendentes = WhatsappQueue::pendentes()->get();

        $this->assertCount(1, $pendentes);
        $this->assertEquals('5511999999999', $pendentes->first()->numero);
    }

    public function test_scope_pendentes_nao_retorna_itens_enviados(): void
    {
        WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511999999999',
            'mensagem'       => 'Já enviado',
            'status'         => 'enviada',
            'tentativas'     => 1,
            'max_tentativas' => 3,
            'enviada_em'     => now(),
        ]);

        $this->assertCount(0, WhatsappQueue::pendentes()->get());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Helpers do Model
    // ──────────────────────────────────────────────────────────────────────────

    public function test_marcar_enviada_atualiza_status_e_timestamp(): void
    {
        $item = WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511999999999',
            'mensagem'       => 'Teste',
            'status'         => 'pendente',
            'tentativas'     => 0,
            'max_tentativas' => 3,
        ]);

        $item->marcarEnviada();
        $item->refresh();

        $this->assertEquals('enviada', $item->status);
        $this->assertNotNull($item->enviada_em);
    }

    public function test_registrar_falha_incrementa_tentativas(): void
    {
        $item = WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511999999999',
            'mensagem'       => 'Teste',
            'status'         => 'pendente',
            'tentativas'     => 1,
            'max_tentativas' => 3,
        ]);

        $item->registrarFalha('Timeout na API');
        $item->refresh();

        $this->assertEquals(2, $item->tentativas);
        $this->assertEquals('pendente', $item->status); // ainda não exauriu
        $this->assertEquals('Timeout na API', $item->ultimo_erro);
    }

    public function test_registrar_falha_muda_status_para_falhou_ao_esgotar(): void
    {
        $item = WhatsappQueue::create([
            'user_id'        => $this->master->id,
            'numero'         => '5511999999999',
            'mensagem'       => 'Teste',
            'status'         => 'pendente',
            'tentativas'     => 2,
            'max_tentativas' => 3,
        ]);

        $item->registrarFalha('Erro fatal');
        $item->refresh();

        $this->assertEquals('falhou', $item->status);
        $this->assertEquals(3, $item->tentativas);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Autorização nas rotas de configuração
    // ──────────────────────────────────────────────────────────────────────────

    public function test_gestor_nao_pode_acessar_reprocessar(): void
    {
        $this->actingAs($this->gestor)
             ->post('/configuracoes/whatsapp/reprocessar')
             ->assertForbidden();
    }

    public function test_colaborador_nao_pode_acessar_reprocessar(): void
    {
        $this->actingAs($this->colaborador)
             ->post('/configuracoes/whatsapp/reprocessar')
             ->assertForbidden();
    }

    public function test_master_pode_acessar_pagina_whatsapp(): void
    {
        $this->actingAs($this->master)
             ->get('/configuracoes/whatsapp')
             ->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Reprocessar via rota — reseta falhadas para pendente
    // ──────────────────────────────────────────────────────────────────────────

    public function test_reprocessar_reseta_falhadas_para_pendente(): void
    {
        Queue::fake(); // Evita executar job de verdade

        // Cria itens com status "falhou"
        for ($i = 0; $i < 3; $i++) {
            WhatsappQueue::create([
                'user_id'        => $this->master->id,
                'numero'         => "551199999999{$i}",
                'mensagem'       => "Mensagem {$i}",
                'status'         => 'falhou',
                'tentativas'     => 3,
                'max_tentativas' => 3,
                'ultimo_erro'    => 'Erro anterior',
            ]);
        }

        $this->actingAs($this->master)
             ->post('/configuracoes/whatsapp/reprocessar')
             ->assertRedirect();

        // Todos devem ter voltado para pendente
        $this->assertEquals(3, WhatsappQueue::where('status', 'pendente')->count());
        $this->assertEquals(0, WhatsappQueue::where('status', 'falhou')->count());

        // Job deve ter sido despachado
        Queue::assertPushed(ProcessarFilaWhatsApp::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Job não faz nada com fila vazia
    // ──────────────────────────────────────────────────────────────────────────

    public function test_job_nao_cria_dados_com_fila_vazia(): void
    {
        $job = new ProcessarFilaWhatsApp();

        // Sem itens pendentes, o job deve sair sem processar nada
        $whatsappServiceMock = $this->createMock(\App\Services\WhatsAppService::class);
        $whatsappServiceMock->expects($this->never())->method('processarFila');

        $job->handle($whatsappServiceMock);

        $this->assertCount(0, WhatsappQueue::all());
    }
}
