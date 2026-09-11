<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Atividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class NotificacaoFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_marcar_notificacao_como_lida()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $user = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        $atividade = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'titulo' => 'Ler isso',
            'descricao' => 'Mensagem importante',
            'lida' => false,
            'url' => '/algo'
        ]);

        // Chama a rota de marcar como lida e redirecionar
        $response = $this->actingAs($user)->get("/notificacoes/{$atividade->id}/ler");

        // Verifica o redirecionamento
        $response->assertRedirect('/algo');

        // Verifica no banco de dados se foi atualizado
        $this->assertDatabaseHas('atividades', [
            'id' => $atividade->id,
            'lida' => 1
        ]);
        
        // Ensure read_at is not null
        $this->assertNotNull($atividade->fresh()->read_at);
    }

    public function test_ler_todas_notificacoes()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $user = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'titulo' => 'Aviso 1',
            'descricao' => 'Descrição 1',
            'lida' => false,
        ]);
        
        Atividade::create([
            'empresa_id' => $user->empresa_id,
            'user_id' => $user->id,
            'titulo' => 'Aviso 2',
            'descricao' => 'Descrição 2',
            'lida' => false,
        ]);

        $response = $this->actingAs($user)->post(route('notificacoes.ler-todas'));
        $response->assertRedirect();

        // Check DB
        $this->assertEquals(2, Atividade::where('user_id', $user->id)->where('lida', true)->count());
    }

    public function test_nao_pode_marcar_notificacao_de_outro_usuario_como_lida()
    {
        $empresa = \App\Models\Empresa::factory()->create();
        $user1 = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        $user2 = User::factory()->create(['perfil_acesso' => 'colaborador', 'empresa_id' => $empresa->id]);
        
        $atividade = Atividade::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user1->id,
            'titulo' => 'Privado',
            'descricao' => 'Mensagem do User 1',
            'lida' => false,
            'url' => '/privado'
        ]);

        // User 2 tenta ler a notificação do User 1
        $response = $this->actingAs($user2)->get("/notificacoes/{$atividade->id}/ler");

        // Deve retornar 404 (abort_if/findOrFail)
        $response->assertStatus(404);

        // Garante que não foi marcada como lida no banco
        $this->assertDatabaseHas('atividades', [
            'id' => $atividade->id,
            'lida' => 0
        ]);
    }
}
