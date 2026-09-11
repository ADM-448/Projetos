<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusPdi;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\PdiGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdiTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Empresa $empresaB;
    private User $master;
    private User $gestor;
    private User $colaborador;
    private User $colaborador2;
    private User $masterB;
    private User $colaboradorB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa  = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);
        $this->empresaB = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

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

        $this->colaborador2 = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->masterB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaboradorB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);
    }

    /**
     * Teste: Um colaborador comum pode ver apenas seus próprios PDIs.
     */
    public function test_colaborador_pode_ver_apenas_seus_proprios_pdis(): void
    {
        // PDI do colaborador logado
        $pdiSelf = Pdi::create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->colaborador->id,
            'gestor_id' => $this->gestor->id,
            'titulo' => 'Meu PDI Pessoal',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        // PDI de outro colaborador
        $pdiOther = Pdi::create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->colaborador2->id,
            'gestor_id' => $this->gestor->id,
            'titulo' => 'PDI do Colega',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        $response = $this->actingAs($this->colaborador)
            ->get(route('pdis.index'));

        $response->assertStatus(200);
        $response->assertSee($pdiSelf->titulo);
        $response->assertDontSee($pdiOther->titulo);
    }

    /**
     * Teste: Um gestor pode criar um PDI individual para o seu liderado.
     */
    public function test_gestor_pode_criar_pdi_individual_para_liderado(): void
    {
        $payload = [
            'tipo_criacao' => 'individual',
            'colaborador_id' => $this->colaborador->id,
            'titulo' => 'PDI Especial Liderado',
            'categoria' => 'Comportamental',
            'acoes' => [
                ['titulo' => 'Ação 1', 'descricao' => 'Descrição 1'],
                ['titulo' => 'Ação 2', 'descricao' => 'Descrição 2'],
            ]
        ];

        $response = $this->actingAs($this->gestor)
            ->post(route('pdis.store'), $payload);

        $response->assertRedirect(route('pdis.index'));

        $this->assertDatabaseHas('pdis', [
            'colaborador_id' => $this->colaborador->id,
            'titulo' => 'PDI Especial Liderado',
            'progresso' => 0,
        ]);

        $this->assertDatabaseHas('pdi_acoes', [
            'titulo' => 'Ação 1',
            'status' => 'pendente',
        ]);
    }

    /**
     * Teste: Um gestor pode criar um PDI em grupo para liderados.
     */
    public function test_gestor_pode_criar_pdi_em_grupo_para_liderados(): void
    {
        $payload = [
            'tipo_criacao' => 'grupo',
            'colaboradores' => [$this->colaborador->id, $this->colaborador2->id],
            'titulo' => 'PDI em Grupo Lote',
            'categoria' => 'Técnico',
            'acoes' => [
                ['titulo' => 'Ação em lote', 'descricao' => 'Descrição lote'],
            ]
        ];

        $response = $this->actingAs($this->gestor)
            ->post(route('pdis.store'), $payload);

        $response->assertRedirect(route('pdis.index'));

        $this->assertDatabaseHas('pdi_grupos', [
            'titulo' => 'PDI em Grupo Lote',
        ]);

        $this->assertDatabaseHas('pdis', [
            'colaborador_id' => $this->colaborador->id,
            'titulo' => 'PDI em Grupo Lote',
        ]);

        $this->assertDatabaseHas('pdis', [
            'colaborador_id' => $this->colaborador2->id,
            'titulo' => 'PDI em Grupo Lote',
        ]);
    }

    /**
     * Teste: O toggle de ação altera status e recalcula progresso.
     */
    public function test_toggle_acao_recalcula_progresso_e_conclui_pdi(): void
    {
        $pdi = Pdi::create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->colaborador->id,
            'gestor_id' => $this->gestor->id,
            'titulo' => 'PDI Progresso',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        $acao1 = $pdi->acoes()->create(['titulo' => 'Acao A', 'status' => 'pendente']);
        $acao2 = $pdi->acoes()->create(['titulo' => 'Acao B', 'status' => 'pendente']);

        // 1. Marca Ação 1 como concluída
        $response = $this->actingAs($this->colaborador)
            ->post(route('pdi-acoes.toggle', $acao1->id), ['status' => 'concluido']);

        $response->assertStatus(302);
        
        $pdi->refresh();
        $this->assertEquals(50, $pdi->progresso);
        $this->assertEquals(StatusPdi::EM_ANDAMENTO, $pdi->status);
        $this->assertNull($pdi->data_conclusao);

        // 2. Marca Ação 2 como concluída (deve atingir 100% e fechar o PDI)
        $response = $this->actingAs($this->colaborador)
            ->post(route('pdi-acoes.toggle', $acao2->id), ['status' => 'concluido']);

        $pdi->refresh();
        $this->assertEquals(100, $pdi->progresso);
        $this->assertEquals(StatusPdi::CONCLUIDO, $pdi->status);
        $this->assertNotNull($pdi->data_conclusao);
    }

    /**
     * Teste: O isolamento de tenant impede visualizações indevidas.
     */
    public function test_isolamento_tenant_impedindo_acesso_a_pdi_de_outra_empresa(): void
    {
        // PDI criado na Empresa B
        $pdiEmpresaB = Pdi::create([
            'empresa_id' => $this->empresaB->id,
            'colaborador_id' => $this->colaboradorB->id,
            'gestor_id' => $this->masterB->id,
            'titulo' => 'PDI Empresa B',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        // Usuário da Empresa A tenta visualizar o PDI da Empresa B
        $response = $this->actingAs($this->colaborador)
            ->get(route('pdis.show', $pdiEmpresaB->id));

        // Deve retornar 403 ou 404 (Como o model possui global scope do tenant, 
        // o Laravel irá levantar 404 ModelNotFoundException porque a consulta não encontra o registro daquele tenant)
        $response->assertStatus(404);
    }

    /**
     * Teste: Comentários no chat de alinhamento do PDI.
     */
    public function test_comentarios_pdi_restritos_a_participantes_autorizados(): void
    {
        $pdi = Pdi::create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $this->colaborador->id,
            'gestor_id' => $this->gestor->id,
            'titulo' => 'PDI Comentarios',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        // Autorizado: Dono do PDI comentando
        $response = $this->actingAs($this->colaborador)
            ->post(route('pdis.comentarios.store', $pdi->id), ['comentario' => 'Estou estudando!']);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pdi_comentarios', [
            'pdi_id' => $pdi->id,
            'user_id' => $this->colaborador->id,
            'comentario' => 'Estou estudando!',
        ]);

        // Não Autorizado: Usuário de outra empresa comentando
        $pdiEmpresaB = Pdi::create([
            'empresa_id' => $this->empresaB->id,
            'colaborador_id' => $this->colaboradorB->id,
            'gestor_id' => $this->masterB->id,
            'titulo' => 'PDI Empresa B',
            'status' => StatusPdi::NAO_INICIADO,
        ]);

        $response = $this->actingAs($this->colaborador)
            ->post(route('pdis.comentarios.store', $pdiEmpresaB->id), ['comentario' => 'Ataque hacker!']);

        // Deve retornar 404 porque a model de PDI da outra empresa é oculta pelo global scope do tenant
        $response->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BUG FIX: Gestor direto do colaborador pode ver PDI criado pelo Master/RH
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Cenário exato do bug relatado:
     * - Master/RH cria o PDI para o colaborador (gestor_id = master->id)
     * - O colaborador tem um gestor direto diferente (colaborador->gestor_id = gestor->id)
     * - O gestor tenta acessar o PDI e recebia 403 — agora deve receber 200
     */
    public function test_gestor_direto_do_colaborador_pode_ver_pdi_criado_pelo_master(): void
    {
        $this->actingAs($this->master);

        // Associa o colaborador ao gestor como gestor direto
        $this->colaborador->update(['gestor_id' => $this->gestor->id]);

        // Master/RH cria o PDI (gestor_id = master, não o gestor direto)
        $pdi = Pdi::create([
            'empresa_id'    => $this->empresa->id,
            'colaborador_id'=> $this->colaborador->id,
            'gestor_id'     => $this->master->id, // Quem criou foi o Master
            'titulo'        => 'PDI criado pelo Master',
            'status'        => StatusPdi::NAO_INICIADO,
        ]);

        // Gestor direto tenta acessar — deve ter permissão (200)
        $this->actingAs($this->gestor)
             ->get(route('pdis.show', $pdi->id))
             ->assertStatus(200);
    }

    /**
     * Garante que um gestor de OUTRA empresa (ou gestor sem vínculo) continua
     * sem acesso ao PDI mesmo com a correção aplicada.
     */
    public function test_gestor_sem_vinculo_com_colaborador_nao_pode_ver_pdi(): void
    {
        $this->actingAs($this->master);

        // Colaborador NÃO tem gestor_id apontando para $this->gestor
        $this->colaborador->update(['gestor_id' => null]);

        $pdi = Pdi::create([
            'empresa_id'    => $this->empresa->id,
            'colaborador_id'=> $this->colaborador->id,
            'gestor_id'     => $this->master->id,
            'titulo'        => 'PDI sem gestor vinculado',
            'status'        => StatusPdi::NAO_INICIADO,
        ]);

        // Gestor não tem vínculo — deve receber 403
        $this->actingAs($this->gestor)
             ->get(route('pdis.show', $pdi->id))
             ->assertForbidden();
    }

    /**
     * Garante que o gestor direto pode comentar no PDI do seu subordinado,
     * mesmo que o PDI tenha sido criado pelo Master.
     */
    public function test_gestor_direto_pode_comentar_pdi_criado_pelo_master(): void
    {
        $this->actingAs($this->master);

        $this->colaborador->update(['gestor_id' => $this->gestor->id]);

        $pdi = Pdi::create([
            'empresa_id'    => $this->empresa->id,
            'colaborador_id'=> $this->colaborador->id,
            'gestor_id'     => $this->master->id,
            'titulo'        => 'PDI para comentar',
            'status'        => StatusPdi::NAO_INICIADO,
        ]);

        $this->actingAs($this->gestor)
             ->post(route('pdis.comentarios.store', $pdi->id), ['comentario' => 'Muito bom progresso!'])
             ->assertRedirect()
             ->assertSessionHas('success');
    }
}
