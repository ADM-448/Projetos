<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use App\Models\User;
use App\Services\AvaliacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garante que excluir uma campanha também remove o que o colaborador vê em "Minhas Avaliações".
 */
class AvaliacaoExclusaoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colaborador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colaborador = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->actingAs($this->master);
    }

    private function criarCampanhaRespondida(string $titulo): Avaliacao
    {
        $competencia = Competencia::create(['nome' => 'Comunicação ' . $titulo]);
        Skill::create(['competencia_id' => $competencia->id, 'nome' => 'Clareza']);

        $avaliacao = app(AvaliacaoService::class)->criar([
            'titulo'       => $titulo,
            'data_inicio'  => now()->toDateString(),
            'data_fim'     => now()->addDays(10)->toDateString(),
            'competencias' => [$competencia->id],
            'avaliados'    => [$this->colaborador->id],
            'avaliadores'  => [],
            'mediadores'   => [],
        ], $this->empresa->id, $this->master->id);

        AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)->update(['status' => 'respondido']);

        return $avaliacao;
    }

    public function test_excluir_campanha_remove_participantes_e_some_para_o_colaborador(): void
    {
        $avaliacao = $this->criarCampanhaRespondida('Campanha Teste 2026');

        $this->assertDatabaseHas('avaliacao_participantes', ['avaliacao_id' => $avaliacao->id]);

        $this->actingAs($this->master)
            ->delete(route('avaliacoes.destroy', $avaliacao->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('avaliacoes', ['id' => $avaliacao->id]);
        $this->assertDatabaseMissing('avaliacao_participantes', ['avaliacao_id' => $avaliacao->id]);

        $this->actingAs($this->colaborador)
            ->get(route('avaliacoes.minhas'))
            ->assertOk()
            ->assertViewHas('participacoes', fn ($participacoes) => $participacoes->isEmpty());
    }

    public function test_excluir_uma_campanha_nao_afeta_outra(): void
    {
        $excluida = $this->criarCampanhaRespondida('Campanha A');
        $mantida  = $this->criarCampanhaRespondida('Campanha B');

        $this->actingAs($this->master)
            ->delete(route('avaliacoes.destroy', $excluida->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('avaliacao_participantes', ['avaliacao_id' => $excluida->id]);
        $this->assertDatabaseHas('avaliacao_participantes', ['avaliacao_id' => $mantida->id]);
    }
}
