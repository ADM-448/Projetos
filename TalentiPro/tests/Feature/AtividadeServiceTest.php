<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Empresa;
use App\Models\User;
use App\Services\AtividadeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: AtividadeServiceTest
 * Cobre: Registro de atividades, capping automático e isolamento por usuário.
 */
class AtividadeServiceTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $gestor;
    private AtividadeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

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

        $this->actingAs($this->master);
        $this->service = app(AtividadeService::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Criação básica
    // ──────────────────────────────────────────────────────────────────────────

    public function test_atividade_e_criada_ao_registrar(): void
    {
        $this->service->registrar(
            $this->empresa->id,
            $this->master->id,
            'Avaliação Criada',
            'Uma nova avaliação foi criada.'
        );

        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->master->id,
            'empresa_id' => $this->empresa->id,
            'titulo'     => 'Avaliação Criada',
        ]);
    }

    public function test_atividade_persiste_todos_campos_corretamente(): void
    {
        $this->service->registrar(
            $this->empresa->id,
            $this->master->id,
            'Título Teste',
            'Descrição Teste',
            'fa-solid fa-star',
            '#ff0000',
            '/avaliacoes/1'
        );

        $this->assertDatabaseHas('atividades', [
            'titulo'     => 'Título Teste',
            'descricao'  => 'Descrição Teste',
            'icone'      => 'fa-solid fa-star',
            'cor'        => '#ff0000',
            'url'        => '/avaliacoes/1',
            'user_id'    => $this->master->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Capping: máximo 10 atividades por usuário
    // ──────────────────────────────────────────────────────────────────────────

    public function test_capping_limita_a_10_atividades_por_usuario(): void
    {
        // Insere 12 atividades para o Master
        for ($i = 1; $i <= 12; $i++) {
            $this->service->registrar(
                $this->empresa->id,
                $this->master->id,
                "Atividade {$i}",
                'Descrição qualquer'
            );
        }

        $total = Atividade::where('user_id', $this->master->id)->count();

        $this->assertEquals(10, $total, 'O capping deve manter exatamente 10 atividades por usuário.');
    }

    public function test_capping_remove_as_mais_antigas_primeiro(): void
    {
        // Insere 11 atividades em sequência
        for ($i = 1; $i <= 11; $i++) {
            $this->service->registrar(
                $this->empresa->id,
                $this->master->id,
                "Atividade {$i}",
                'Descrição'
            );
        }

        // O sistema deve manter exatamente 10 atividades (capping ativo)
        $count = \App\Models\Atividade::where('user_id', $this->master->id)->count();
        $this->assertEquals(10, $count, 'O capping deve manter exatamente 10 atividades por usuário.');

        // A última inserida deve ter chegado ao banco antes do capping
        // O capping garante que nunca excede 10, preservando sempre as mais recentes
        $atividadesRestantes = \App\Models\Atividade::where('user_id', $this->master->id)
            ->orderBy('created_at', 'desc')
            ->pluck('titulo')
            ->toArray();

        // A Atividade 11 (última inserida) deve estar na lista das mantidas
        $this->assertContains('Atividade 11', $atividadesRestantes);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Isolamento por usuário
    // ──────────────────────────────────────────────────────────────────────────

    public function test_atividades_de_usuarios_diferentes_nao_se_misturam(): void
    {
        // Registra 5 atividades para o Master e 5 para o Gestor
        for ($i = 1; $i <= 5; $i++) {
            $this->service->registrar($this->empresa->id, $this->master->id, "Master {$i}", 'desc');
            $this->service->registrar($this->empresa->id, $this->gestor->id, "Gestor {$i}", 'desc');
        }

        $atividadesMaster = Atividade::where('user_id', $this->master->id)->count();
        $atividadesGestor = Atividade::where('user_id', $this->gestor->id)->count();

        $this->assertEquals(5, $atividadesMaster);
        $this->assertEquals(5, $atividadesGestor);
    }

    public function test_capping_de_um_usuario_nao_afeta_outro(): void
    {
        // Insere 12 para Master (dispara capping) e 3 para Gestor
        for ($i = 1; $i <= 12; $i++) {
            $this->service->registrar($this->empresa->id, $this->master->id, "Master {$i}", 'desc');
        }
        for ($i = 1; $i <= 3; $i++) {
            $this->service->registrar($this->empresa->id, $this->gestor->id, "Gestor {$i}", 'desc');
        }

        $this->assertEquals(10, Atividade::where('user_id', $this->master->id)->count());
        $this->assertEquals(3, Atividade::where('user_id', $this->gestor->id)->count());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Stub do WhatsApp (não deve quebrar com WHATSAPP_ENABLED=false)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_whatsapp_desativado_nao_quebra_o_registrar(): void
    {
        // Garante que está desativado (padrão local)
        config(['whatsapp.enabled' => false]);

        // Não deve lançar exceção
        $this->service->registrar(
            $this->empresa->id,
            $this->master->id,
            'Teste WhatsApp Stub',
            'Não deve quebrar'
        );

        $this->assertDatabaseHas('atividades', [
            'titulo'  => 'Teste WhatsApp Stub',
            'user_id' => $this->master->id,
        ]);
    }
}
