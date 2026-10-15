<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\UserRole;
use App\Models\Avaliacao;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultorAcessoMultiEmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_cadastrar_consultor_com_multiplas_empresas(): void
    {
        $admin = User::factory()->create([
            'perfil_acesso' => UserRole::ADMIN,
            'empresa_id'    => null,
        ]);

        $empresa1 = Empresa::factory()->create(['nome' => 'Empresa Alfa', 'status' => 'ativa']);
        $empresa2 = Empresa::factory()->create(['nome' => 'Empresa Beta', 'status' => 'ativa']);

        $response = $this->actingAs($admin)->post(route('admin.consultores.store'), [
            'name'       => 'Dr. Consultor',
            'email'      => 'consultor@rh.com',
            'password'   => 'senha123',
            'status'     => 'ativo',
            'empresas'   => [$empresa1->id, $empresa2->id],
        ]);

        $response->assertRedirect(route('admin.consultores.index'));

        $consultor = User::where('email', 'consultor@rh.com')->first();
        $this->assertNotNull($consultor);
        $this->assertEquals(UserRole::CONSULTOR, $consultor->perfil_acesso);
        $this->assertTrue($consultor->isConsultor());
        $this->assertCount(2, $consultor->empresasConsultoria);
    }

    public function test_consultor_pode_alternar_entre_suas_empresas_vinculadas(): void
    {
        $empresa1 = Empresa::factory()->create(['nome' => 'Empresa Alfa', 'status' => 'ativa']);
        $empresa2 = Empresa::factory()->create(['nome' => 'Empresa Beta', 'status' => 'ativa']);
        $empresa3 = Empresa::factory()->create(['nome' => 'Empresa Proibida', 'status' => 'ativa']);

        $consultor = User::factory()->create([
            'perfil_acesso' => UserRole::CONSULTOR,
            'empresa_id'    => $empresa1->id,
        ]);

        $consultor->empresasConsultoria()->sync([$empresa1->id, $empresa2->id]);

        // Alterna com sucesso para Empresa 2
        $response = $this->actingAs($consultor)->post(route('consultor.selecionar-empresa'), [
            'empresa_id' => $empresa2->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $consultor->refresh();
        $this->assertEquals($empresa2->id, $consultor->empresa_id);

        // Tentativa de alternar para Empresa 3 (não vinculada) deve ser barrada com 403
        $responseProibida = $this->actingAs($consultor)->post(route('consultor.selecionar-empresa'), [
            'empresa_id' => $empresa3->id,
        ]);

        $responseProibida->assertForbidden();
    }

    public function test_consultor_visualiza_dashboard_e_dados_da_empresa_ativa(): void
    {
        $empresa = Empresa::factory()->create(['nome' => 'Empresa Consultoria', 'status' => 'ativa']);

        $consultor = User::factory()->create([
            'perfil_acesso' => UserRole::CONSULTOR,
            'empresa_id'    => $empresa->id,
        ]);
        $consultor->empresasConsultoria()->sync([$empresa->id]);

        $response = $this->actingAs($consultor)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_consultor_nao_tem_permissao_para_criar_avaliacao_nem_pdi(): void
    {
        $empresa = Empresa::factory()->create(['nome' => 'Empresa RH', 'status' => 'ativa']);

        $consultor = User::factory()->create([
            'perfil_acesso' => UserRole::CONSULTOR,
            'empresa_id'    => $empresa->id,
        ]);
        $consultor->empresasConsultoria()->sync([$empresa->id]);

        // Tentativa de abrir tela de criação de PDI
        $responsePdi = $this->actingAs($consultor)->get(route('pdis.create'));
        $responsePdi->assertForbidden();

        // Tentativa de acessar tela de criação de Avaliação (bloqueada pelo middleware role:master,gestor)
        $responseAvaliacao = $this->actingAs($consultor)->get(route('avaliacoes.create'));
        $responseAvaliacao->assertForbidden();
    }
}
