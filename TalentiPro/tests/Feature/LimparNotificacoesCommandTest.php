<?php

namespace Tests\Feature;

use App\Models\Atividade;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LimparNotificacoesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function criarNotificacao(User $user, \DateTimeInterface $criadaEm, bool $lida = false): Atividade
    {
        $atividade = Atividade::create([
            'empresa_id' => $user->empresa_id,
            'user_id'    => $user->id,
            'titulo'     => 'Notificação de teste',
            'descricao'  => 'Descrição',
            'lida'       => $lida,
        ]);

        // created_at não é fillable: força a data via query direta
        Atividade::where('id', $atividade->id)->update(['created_at' => $criadaEm]);

        return $atividade->fresh();
    }

    private function criarUsuario(): User
    {
        $empresa = Empresa::factory()->create();

        return User::factory()->create([
            'perfil_acesso' => 'colaborador',
            'empresa_id'    => $empresa->id,
        ]);
    }

    public function test_remove_notificacoes_com_mais_de_3_dias(): void
    {
        $user = $this->criarUsuario();
        $antiga = $this->criarNotificacao($user, now()->subDays(4));

        $this->artisan('notificacoes:limpar')->assertExitCode(0);

        $this->assertDatabaseMissing('atividades', ['id' => $antiga->id]);
    }

    public function test_remove_antigas_mesmo_nao_lidas(): void
    {
        $user = $this->criarUsuario();
        $naoLida = $this->criarNotificacao($user, now()->subDays(10), false);
        $lida    = $this->criarNotificacao($user, now()->subDays(10), true);

        $this->artisan('notificacoes:limpar')->assertExitCode(0);

        $this->assertDatabaseMissing('atividades', ['id' => $naoLida->id]);
        $this->assertDatabaseMissing('atividades', ['id' => $lida->id]);
    }

    public function test_mantem_notificacoes_recentes(): void
    {
        $user = $this->criarUsuario();
        $hoje   = $this->criarNotificacao($user, now());
        $doisDias = $this->criarNotificacao($user, now()->subDays(2));

        $this->artisan('notificacoes:limpar')->assertExitCode(0);

        $this->assertDatabaseHas('atividades', ['id' => $hoje->id]);
        $this->assertDatabaseHas('atividades', ['id' => $doisDias->id]);
    }

    public function test_opcao_dias_personalizada(): void
    {
        $user = $this->criarUsuario();
        $cincoDias = $this->criarNotificacao($user, now()->subDays(5));

        $this->artisan('notificacoes:limpar --dias=7')->assertExitCode(0);
        $this->assertDatabaseHas('atividades', ['id' => $cincoDias->id]);

        $this->artisan('notificacoes:limpar --dias=2')->assertExitCode(0);
        $this->assertDatabaseMissing('atividades', ['id' => $cincoDias->id]);
    }

    public function test_rejeita_dias_invalido(): void
    {
        $this->artisan('notificacoes:limpar --dias=0')->assertExitCode(1);
    }

    public function test_funciona_sem_nenhuma_notificacao(): void
    {
        $this->artisan('notificacoes:limpar')->assertExitCode(0);
    }
}
