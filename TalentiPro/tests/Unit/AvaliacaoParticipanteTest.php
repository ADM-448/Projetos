<?php

namespace Tests\Unit;

use App\Enums\StatusAvaliacao;
use App\Enums\TipoAvaliacao;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes unitários do model AvaliacaoParticipante.
 *
 * Cobre os helpers de status, relacionamentos e a lógica de
 * contagem que alimenta o modal do dashboard.
 */
class AvaliacaoParticipanteTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Avaliacao $avaliacao;
    protected User $avaliado;
    protected User $avaliador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        $master = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'perfil_acesso' => 'master',
        ]);

        $this->avaliacao = Avaliacao::create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Avaliação Unit Test',
            'data_inicio' => now(),
            'data_fim' => now()->addDays(5),
            'status' => StatusAvaliacao::ANDAMENTO,
            'created_by' => $master->id,
        ]);

        $this->avaliado = User::factory()->create(['empresa_id' => $this->empresa->id]);
        $this->avaliador = User::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    // ─── Status ────────────────────────────────────────────────────────

    /**
     * Participante recém-criado deve ter status 'pendente'.
     */
    public function test_participante_comeca_como_pendente(): void
    {
        $p = AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $this->avaliado->id,
            'avaliador_id' => $this->avaliador->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::GESTOR,
        ]);

        $this->assertEquals('pendente', $p->status);
    }

    /**
     * O status correto após responder deve ser 'respondido' (não 'concluida').
     */
    public function test_status_respondido_e_o_valor_correto(): void
    {
        $p = AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $this->avaliado->id,
            'avaliador_id' => $this->avaliador->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::GESTOR,
        ]);

        // Simula a ação de responder
        $p->update(['status' => 'respondido']);

        $this->assertEquals('respondido', $p->fresh()->status);
        $this->assertNotEquals('concluida', $p->fresh()->status);
    }

    // ─── Contagem de Respondidos ───────────────────────────────────────

    /**
     * A contagem filtrada por 'respondido' deve retornar o valor correto.
     * Este é o teste que falha com o bug antigo (filtrava por 'concluida').
     */
    public function test_contagem_por_status_respondido_funciona(): void
    {
        // Cria 3 participantes
        $participantes = collect();
        $tipos = [TipoAvaliacao::AUTOAVALIACAO, TipoAvaliacao::GESTOR, TipoAvaliacao::MEDIADOR];

        foreach ($tipos as $tipo) {
            $avaliador = $tipo === TipoAvaliacao::AUTOAVALIACAO
                ? $this->avaliado
                : User::factory()->create(['empresa_id' => $this->empresa->id]);

            $participantes->push(AvaliacaoParticipante::create([
                'avaliacao_id' => $this->avaliacao->id,
                'avaliado_id' => $this->avaliado->id,
                'avaliador_id' => $avaliador->id,
                'status' => 'pendente',
                'tipo' => $tipo,
            ]));
        }

        // Confirma que está tudo em 0
        $total = $participantes->count();
        $respondidos = $participantes->where('status', 'respondido')->count();
        $this->assertEquals(3, $total);
        $this->assertEquals(0, $respondidos);

        // Simula 2 respostas
        $participantes[0]->update(['status' => 'respondido']);
        $participantes[1]->update(['status' => 'respondido']);

        // Recarrega da base para ter os dados atualizados
        $participantesRecarregados = AvaliacaoParticipante::where('avaliacao_id', $this->avaliacao->id)
            ->where('avaliado_id', $this->avaliado->id)
            ->get();

        $respondidosAtualizado = $participantesRecarregados->where('status', 'respondido')->count();
        $this->assertEquals(2, $respondidosAtualizado);

        // Bug antigo: filtrando por 'concluida' deveria retornar 0
        $concluidos = $participantesRecarregados->where('status', 'concluida')->count();
        $this->assertEquals(0, $concluidos, 'Nunca deve haver registros com status concluida em avaliacao_participantes');
    }

    // ─── Relacionamentos ───────────────────────────────────────────────

    /**
     * Participante deve ter relação correta com avaliação, avaliado e avaliador.
     */
    public function test_relacionamentos_do_participante(): void
    {
        $p = AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $this->avaliado->id,
            'avaliador_id' => $this->avaliador->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::MEDIADOR,
        ]);

        $p->load(['avaliacao', 'avaliado', 'avaliador']);

        $this->assertInstanceOf(Avaliacao::class, $p->avaliacao);
        $this->assertEquals($this->avaliacao->id, $p->avaliacao->id);

        $this->assertInstanceOf(User::class, $p->avaliado);
        $this->assertEquals($this->avaliado->id, $p->avaliado->id);

        $this->assertInstanceOf(User::class, $p->avaliador);
        $this->assertEquals($this->avaliador->id, $p->avaliador->id);
    }

    /**
     * O agrupamento groupBy('avaliado_id') deve funcionar corretamente
     * (mesma lógica usada no blade do modal).
     */
    public function test_agrupamento_por_avaliado_id(): void
    {
        $avaliado2 = User::factory()->create(['empresa_id' => $this->empresa->id]);

        // 2 participantes para avaliado 1
        AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $this->avaliado->id,
            'avaliador_id' => $this->avaliado->id,
            'status' => 'respondido',
            'tipo' => TipoAvaliacao::AUTOAVALIACAO,
        ]);
        AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $this->avaliado->id,
            'avaliador_id' => $this->avaliador->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::GESTOR,
        ]);

        // 1 participante para avaliado 2
        AvaliacaoParticipante::create([
            'avaliacao_id' => $this->avaliacao->id,
            'avaliado_id' => $avaliado2->id,
            'avaliador_id' => $avaliado2->id,
            'status' => 'pendente',
            'tipo' => TipoAvaliacao::AUTOAVALIACAO,
        ]);

        $participantes = $this->avaliacao->participantes;
        $agrupados = $participantes->groupBy('avaliado_id');

        $this->assertCount(2, $agrupados); // 2 avaliados distintos

        // Avaliado 1: 2 participantes, 1 respondido
        $grupoAvaliado1 = $agrupados[$this->avaliado->id];
        $this->assertCount(2, $grupoAvaliado1);
        $this->assertEquals(1, $grupoAvaliado1->where('status', 'respondido')->count());

        // Avaliado 2: 1 participante, 0 respondidos
        $grupoAvaliado2 = $agrupados[$avaliado2->id];
        $this->assertCount(1, $grupoAvaliado2);
        $this->assertEquals(0, $grupoAvaliado2->where('status', 'respondido')->count());
    }
}
