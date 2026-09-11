<?php

namespace Tests\Feature;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColaboradorRulesTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    private User $masterUser;

    private User $outroUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar empresa ativa
        $this->empresa = Empresa::create([
            'nome' => 'Empresa Teste',
            'cnpj' => '11111111000111',
            'status' => StatusEmpresa::ATIVA,
        ]);

        // Criar usuário Master (logado)
        $this->masterUser = User::create([
            'name' => 'Master User',
            'email' => 'master@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresa->id,
        ]);

        // Criar outro usuário comum
        $this->outroUser = User::create([
            'name' => 'Colaborador Comum',
            'email' => 'colab@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::COLABORADOR,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    /**
     * Teste: O usuário não pode excluir a sua própria conta.
     */
    public function test_user_cannot_delete_themselves(): void
    {
        $this->actingAs($this->masterUser);

        // Tenta enviar DELETE para si mesmo
        $response = $this->delete("/colaboradores/{$this->masterUser->id}");

        // Deve retornar com erro na sessão (redirecionamento de volta)
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Não é possível excluir: Você não pode excluir a sua própria conta.');

        // Garante que o usuário ainda existe no banco
        $this->assertDatabaseHas('users', [
            'id' => $this->masterUser->id,
        ]);
    }

    /**
     * Teste: O usuário Master pode excluir outro colaborador normalmente.
     */
    public function test_master_can_delete_other_colaborador(): void
    {
        $this->actingAs($this->masterUser);

        // Exclui o outro usuário
        $response = $this->delete("/colaboradores/{$this->outroUser->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Colaborador excluído com sucesso!');

        // Confirma que foi excluído do banco
        $this->assertDatabaseMissing('users', [
            'id' => $this->outroUser->id,
        ]);
    }

    /**
     * Teste: Ao atualizar a si mesmo, o usuário Master não pode rebaixar seu perfil_acesso ou desativar seu status (prevenção de auto-lockout).
     */
    public function test_user_cannot_downgrade_own_role_or_deactivate_own_status(): void
    {
        $this->actingAs($this->masterUser);

        // Tenta editar a si mesmo mudando perfil para colaborador e status para inativo
        $response = $this->put("/colaboradores/{$this->masterUser->id}", [
            'name' => 'Master Alterado',
            'email' => 'master@empresa.com',
            'perfil_acesso' => 'colaborador', // Tenta rebaixar
            'status' => 'inativo', // Tenta desativar
        ]);

        $response->assertStatus(302);

        // Recarrega o usuário do banco
        $this->masterUser->refresh();

        // Confirma que o nome mudou, mas o perfil de acesso e status continuam inalterados
        $this->assertEquals('Master Alterado', $this->masterUser->name);
        $this->assertEquals(UserRole::MASTER, $this->masterUser->perfil_acesso);
        $this->assertEquals(StatusUsuario::ATIVO, $this->masterUser->status);
    }

    /**
     * Teste: Um gestor que possui colaboradores vinculados a ele (subordinados) não pode ser deletado.
     */
    public function test_cannot_delete_gestor_with_subordinates(): void
    {
        // Torna o outroUser subordinado do masterUser
        $this->outroUser->update([
            'gestor_id' => $this->masterUser->id,
        ]);

        // Cria um segundo usuário Master para tentar excluir o primeiro Master (que é gestor e tem subordinado)
        $outroMaster = User::create([
            'name' => 'Outro Master',
            'email' => 'outromaster@empresa.com',
            'password' => bcrypt('password123'),
            'perfil_acesso' => UserRole::MASTER,
            'status' => StatusUsuario::ATIVO,
            'empresa_id' => $this->empresa->id,
        ]);

        $this->actingAs($outroMaster);

        // Tenta deletar o primeiro masterUser (que agora tem subordinado)
        $response = $this->delete("/colaboradores/{$this->masterUser->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Não é possível excluir: Este gestor possui colaboradores associados a ele.');

        // Garante que o gestor ainda existe
        $this->assertDatabaseHas('users', [
            'id' => $this->masterUser->id,
        ]);
    }
}
