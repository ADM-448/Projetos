<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Solicitacao;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitacaoContarPendentesTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_conta_todas_solicitacoes_pendentes_da_empresa()
    {
        $empresa = Empresa::factory()->create();

        // 1 Master
        $master = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil_acesso' => UserRole::MASTER
        ]);

        // 1 Gestor e 2 Colaboradores
        $gestor = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => UserRole::GESTOR]);
        $colab1 = User::factory()->create(['empresa_id' => $empresa->id, 'gestor_id' => $gestor->id, 'perfil_acesso' => UserRole::COLABORADOR]);
        
        $outroGestor = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => UserRole::GESTOR]);
        $colab2 = User::factory()->create(['empresa_id' => $empresa->id, 'gestor_id' => $outroGestor->id, 'perfil_acesso' => UserRole::COLABORADOR]);

        // Solicitações Pendentes (uma para colab1 e outra para colab2)
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colab1->id, 'status' => 'pendente', 'tipo' => 'reabertura', 'justificativa' => 'teste']);
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colab2->id, 'status' => 'pendente', 'tipo' => 'reabertura', 'justificativa' => 'teste']);
        // Uma recusada que não deve entrar na conta
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colab1->id, 'status' => 'recusada', 'tipo' => 'reabertura', 'justificativa' => 'teste']);

        // Logando como master (BelongsToEmpresa boot via tenant scope)
        $this->actingAs($master);

        // Se passar gestorId = null, ele conta tudo da empresa
        $totalPendentes = Solicitacao::contarPendentes(null, null);

        $this->assertEquals(2, $totalPendentes);
    }

    public function test_gestor_conta_apenas_solicitacoes_dos_seus_liderados()
    {
        $empresa = Empresa::factory()->create();

        // 2 Gestores
        $gestorA = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => UserRole::GESTOR]);
        $gestorB = User::factory()->create(['empresa_id' => $empresa->id, 'perfil_acesso' => UserRole::GESTOR]);

        $colabA = User::factory()->create(['empresa_id' => $empresa->id, 'gestor_id' => $gestorA->id, 'perfil_acesso' => UserRole::COLABORADOR]);
        $colabB = User::factory()->create(['empresa_id' => $empresa->id, 'gestor_id' => $gestorB->id, 'perfil_acesso' => UserRole::COLABORADOR]);

        // Solicitações
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colabA->id, 'status' => 'pendente', 'tipo' => 'reabertura', 'justificativa' => 'teste']);
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colabA->id, 'status' => 'pendente', 'tipo' => 'reabertura', 'justificativa' => 'teste']);
        Solicitacao::create(['empresa_id' => $empresa->id, 'user_id' => $colabB->id, 'status' => 'pendente', 'tipo' => 'reabertura', 'justificativa' => 'teste']);

        $this->actingAs($gestorA);

        // Gestor A pede para contar as pendentes da equipe dele
        $totalPendentesGestorA = Solicitacao::contarPendentes(null, $gestorA->id);

        $this->assertEquals(2, $totalPendentesGestorA);
    }
}
