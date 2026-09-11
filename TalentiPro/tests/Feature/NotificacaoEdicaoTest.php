<?php

namespace Tests\Feature;

use App\Enums\StatusAvaliacao;
use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\TipoAvaliacao;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\Competencia;
use App\Models\Empresa;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite: NotificacaoEdicaoTest
 *
 * Garante que ao EDITAR uma avaliação, PDI ou reunião:
 *   - Participantes já existentes NÃO recebem re-notificação
 *   - Apenas participantes NOVOS (adicionados na edição) recebem notificação
 *   - Participantes REMOVIDOS não recebem notificação
 */
class NotificacaoEdicaoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $colab1;
    private User $colab2;
    private User $colab3;
    private User $gestor;
    private Competencia $competencia;
    private Skill $skill;

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

        $this->colab1 = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colab2 = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->colab3 = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::COLABORADOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->actingAs($this->master);
        $this->competencia = Competencia::create(['nome' => 'Comunicação']);
        $this->skill       = Skill::create(['competencia_id' => $this->competencia->id, 'nome' => 'Ouve ativamente']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 1. AVALIAÇÃO — Criação gera notificações
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao criar uma avaliação, cada participante deve receber uma atividade
     * (notificação interna) no banco de dados.
     */
    public function test_criacao_avaliacao_gera_atividades_para_todos_participantes(): void
    {
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Q3',
            'descricao'    => 'Teste criação.',
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(30)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id],
            'avaliados'    => [$this->colab1->id],
            'mediadores'   => [],
            'filtros'      => [],
        ];

        // Antes da criação: zero atividades
        $this->assertDatabaseCount('atividades', 0);

        $this->actingAs($this->master)->post(route('avaliacoes.store'), $payload);

        // Participantes gerados: autoavaliacao (colab1) + gestor avaliando colab1
        // → colab1 recebe 1 atividade; gestor recebe 1 atividade
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->colab1->id,
            'empresa_id' => $this->empresa->id,
        ]);

        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->gestor->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 2. AVALIAÇÃO — Edição SEM mudança de participantes: ZERO notificações novas
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao editar dados básicos (título, datas) sem alterar participantes,
     * nenhuma nova atividade deve ser criada.
     */
    public function test_edicao_avaliacao_sem_mudanca_de_participantes_nao_gera_nova_atividade(): void
    {
        // 1. Cria a avaliação via service (simula criação real com notificações)
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Original',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        // Insere participantes diretamente (sem notificação, para isolar o teste de edição)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->gestor->id,
            'tipo'         => TipoAvaliacao::GESTOR,
            'status'       => 'pendente',
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        // Limpa qualquer atividade anterior para medir apenas as da edição
        Atividade::query()->delete();
        $this->assertDatabaseCount('atividades', 0);

        // 2. Edita sem mudar participantes (só título e datas)
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Editada',
            'descricao'    => 'Descrição alterada.',
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(20)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id],    // mesmo gestor
            'avaliados'    => [$this->colab1->id],    // mesmo avaliado
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $response = $this->actingAs($this->master)
                         ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // Nenhuma atividade nova deve ter sido criada
        $this->assertDatabaseCount('atividades', 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 3. AVALIAÇÃO — Edição COM adição de novo participante: apenas o novo recebe
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao editar adicionando um novo avaliado (colab2), apenas colab2 deve
     * receber a notificação. colab1 (já existente) NÃO deve ser re-notificado.
     */
    public function test_edicao_avaliacao_adicionando_participante_notifica_apenas_o_novo(): void
    {
        // 1. Cria avaliação com colab1
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Inicial',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        // Limpa atividades existentes
        Atividade::query()->delete();

        // 2. Edita adicionando colab2 como novo avaliado (gestor é avaliador obrigatório)
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Expandida',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id], // avaliador obrigatório (min:1)
            'avaliados'    => [$this->colab1->id, $this->colab2->id], // colab2 é novo
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $response = $this->actingAs($this->master)
             ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // colab2 deve ter recebido uma atividade (autoavaliação)
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->colab2->id,
            'empresa_id' => $this->empresa->id,
        ]);

        // colab1 NÃO deve ter recebido nova atividade (já era participante)
        $this->assertDatabaseMissing('atividades', [
            'user_id' => $this->colab1->id,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 4. AVALIAÇÃO — Edição COM remoção de participante: ninguém deve ser notificado
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao remover um participante na edição, nenhuma notificação é enviada.
     * O participante removido simplesmente é apagado do banco sem notificação.
     */
    public function test_edicao_avaliacao_removendo_participante_nao_gera_atividade(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Com Dois',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        // colab1 — autoavaliação
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        // colab2 — autoavaliação (será removido)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab2->id,
            'avaliador_id' => $this->colab2->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        // gestor avaliando colab1 — já existe (mantido na edição, sem re-notificação)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->gestor->id,
            'tipo'         => TipoAvaliacao::GESTOR,
            'status'       => 'pendente',
        ]);

        // gestor avaliando colab2 — também removido quando colab2 sair
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab2->id,
            'avaliador_id' => $this->gestor->id,
            'tipo'         => TipoAvaliacao::GESTOR,
            'status'       => 'pendente',
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        Atividade::query()->delete();

        // Remove colab2 da lista de avaliados (gestor permanece como avaliador, já existia)
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Com Um',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id], // gestor já existia, não é novo
            'avaliados'    => [$this->colab1->id], // apenas colab1 permanece
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $response = $this->actingAs($this->master)
             ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // Nenhuma atividade nova deve ter sido criada (todos os participantes restantes já existiam)
        $this->assertDatabaseCount('atividades', 0);

        // colab2 deve ter sido removido dos participantes
        $this->assertDatabaseMissing('avaliacao_participantes', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab2->id,
        ]);

        // colab1 permanece no banco com status intacto
        $this->assertDatabaseHas('avaliacao_participantes', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'status'       => 'pendente',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 5. AVALIAÇÃO — Status do participante existente é preservado após edição
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Se um participante já respondeu (status = 'respondido'), editar a
     * avaliação sem removê-lo deve preservar esse status.
     */
    public function test_edicao_avaliacao_preserva_status_respondido_do_participante_existente(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação com Resposta',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        // Participante com status 'respondido' (já respondeu)
        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'respondido', // já respondeu!
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Atualizada',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(15)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [],
            'avaliados'    => [$this->colab1->id], // mantém o mesmo avaliado
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $this->actingAs($this->master)
             ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        // Status do colab1 deve continuar 'respondido' (não foi resetado)
        $this->assertDatabaseHas('avaliacao_participantes', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'status'       => 'respondido',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 6. AVALIAÇÃO — Edição adiciona mediador novo: mediador recebe notificação
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao adicionar um novo mediador na edição, o mediador deve receber
     * uma notificação (atividade) para cada avaliado que ele passou a acompanhar.
     */
    public function test_edicao_avaliacao_adicionando_mediador_notifica_apenas_o_mediador_novo(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Sem Mediador',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        Atividade::query()->delete();

        // Adiciona mediador (colab2) na edição; master é o avaliador obrigatório
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Com Mediador',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->master->id], // avaliador obrigatório (min:1)
            'avaliados'    => [$this->colab1->id],
            'mediadores'   => [$this->gestor->id], // gestor é novo mediador
            'filtros'      => [],
        ];

        $response = $this->actingAs($this->master)
             ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // O mediador (gestor) deve ter recebido uma atividade
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->gestor->id,
            'empresa_id' => $this->empresa->id,
        ]);

        // colab1 (já existia como autoavaliação) NÃO deve ter recebido nova atividade
        $this->assertDatabaseMissing('atividades', [
            'user_id' => $this->colab1->id,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 7. AVALIAÇÃO — Edição com substituição de avaliado: só o novo recebe notificação
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Remove colab1 e adiciona colab3. Apenas colab3 recebe notificação.
     * colab1 não recebe nada ao ser removido.
     * colab2 (não estava, não foi adicionado) não recebe nada.
     */
    public function test_edicao_avaliacao_substituindo_avaliado_notifica_apenas_o_novo(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'titulo'      => 'Avaliação Substituição',
            'data_inicio' => now(),
            'data_fim'    => now()->addDays(10),
            'status'      => StatusAvaliacao::ANDAMENTO,
            'created_by'  => $this->master->id,
        ]);

        AvaliacaoParticipante::create([
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
            'avaliador_id' => $this->colab1->id,
            'tipo'         => TipoAvaliacao::AUTOAVALIACAO,
            'status'       => 'pendente',
        ]);

        $avaliacao->competencias()->attach($this->competencia->id);

        Atividade::query()->delete();

        // Substitui colab1 por colab3 (gestor é avaliador obrigatório)
        $payload = [
            'grau' => '360',
            'titulo'       => 'Avaliação Substituição',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(10)->format('Y-m-d'),
            'competencias' => [$this->competencia->id],
            'avaliadores'  => [$this->gestor->id], // avaliador obrigatório (min:1)
            'avaliados'    => [$this->colab3->id], // colab3 substitui colab1
            'mediadores'   => [],
            'filtros'      => [],
        ];

        $response = $this->actingAs($this->master)
             ->put(route('avaliacoes.update', $avaliacao->id), $payload);

        $response->assertRedirect(route('avaliacoes.index'));

        // colab3 (novo) recebe atividade (autoavaliação)
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->colab3->id,
            'empresa_id' => $this->empresa->id,
        ]);

        // colab1 (removido) não recebe atividade
        $this->assertDatabaseMissing('atividades', [
            'user_id' => $this->colab1->id,
        ]);

        // colab1 deve ter sido removido dos participantes
        $this->assertDatabaseMissing('avaliacao_participantes', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab1->id,
        ]);

        // colab3 deve ter sido inserido
        $this->assertDatabaseHas('avaliacao_participantes', [
            'avaliacao_id' => $avaliacao->id,
            'avaliado_id'  => $this->colab3->id,
            'status'       => 'pendente',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 8. PDI — Criação gera notificação para o colaborador
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao criar um PDI individual via rota, o colaborador deve receber
     * uma atividade no banco.
     */
    public function test_criacao_pdi_individual_gera_atividade_para_colaborador(): void
    {
        $this->assertDatabaseCount('atividades', 0);

        $payload = [
            'grau' => '360',
            'tipo_criacao'   => 'individual',
            'colaborador_id' => $this->colab1->id,
            'titulo'         => 'PDI Desenvolvimento PHP',
            'descricao'      => 'Aprender Laravel.',
            'data_inicio'    => now()->format('Y-m-d'),
            'data_fim'       => now()->addDays(90)->format('Y-m-d'),
            'acoes'          => [
                ['titulo' => 'Estudar docs', 'descricao' => 'Ler documentação'],
            ],
        ];

        $this->actingAs($this->master)->post(route('pdis.store'), $payload);

        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->colab1->id,
            'empresa_id' => $this->empresa->id,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 9. PDI — Edição individual NÃO gera re-notificação
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao editar um PDI individual (título, datas, ações), nenhuma nova
     * atividade de notificação deve ser criada.
     */
    public function test_edicao_pdi_individual_nao_gera_re_notificacao(): void
    {
        // Cria o PDI diretamente no banco sem notificação
        $pdi = \App\Models\Pdi::create([
            'empresa_id'     => $this->empresa->id,
            'colaborador_id' => $this->colab1->id,
            'gestor_id'      => $this->master->id,
            'titulo'         => 'PDI Original',
            'status'         => 'nao_iniciado',
            'progresso'      => 0,
        ]);

        $acao = $pdi->acoes()->create([
            'titulo'  => 'Ação Original',
            'status'  => 'pendente',
        ]);

        // Limpa atividades
        Atividade::query()->delete();

        $payload = [
            'grau' => '360',
            'tipo_criacao'   => 'individual',
            'colaborador_id' => $this->colab1->id,
            'titulo'         => 'PDI Atualizado',
            'descricao'      => 'Descrição nova.',
            'data_inicio'    => now()->format('Y-m-d'),
            'data_fim'       => now()->addDays(60)->format('Y-m-d'),
            'acoes'          => [
                ['id' => $acao->id, 'titulo' => 'Ação Atualizada', 'descricao' => ''],
            ],
        ];

        $response = $this->actingAs($this->master)
                         ->put(route('pdis.update', $pdi->id), $payload);

        $response->assertRedirect(route('pdis.index'));

        // Nenhuma atividade nova deve ter sido criada
        $this->assertDatabaseCount('atividades', 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 10. PDI Grupo — Edição com novo colaborador notifica apenas o novo
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ao editar um lote de PDIs adicionando um colaborador novo (colab2),
     * apenas colab2 deve receber notificação. colab1 (já existia) não recebe.
     */
    public function test_edicao_pdi_grupo_novo_colaborador_notifica_apenas_o_novo(): void
    {
        // Cria grupo com colab1
        $grupo = \App\Models\PdiGrupo::create([
            'empresa_id' => $this->empresa->id,
            'gestor_id'  => $this->master->id,
            'titulo'     => 'Grupo PDI',
        ]);

        $pdiColab1 = \App\Models\Pdi::create([
            'empresa_id'     => $this->empresa->id,
            'colaborador_id' => $this->colab1->id,
            'gestor_id'      => $this->master->id,
            'pdi_grupo_id'   => $grupo->id,
            'titulo'         => 'Grupo PDI',
            'status'         => 'nao_iniciado',
            'progresso'      => 0,
        ]);

        $pdiColab1->acoes()->create(['titulo' => 'Ação 1', 'status' => 'pendente']);

        // Limpa atividades
        Atividade::query()->delete();

        $payload = [
            'grau' => '360',
            'tipo_criacao' => 'grupo',
            'titulo'       => 'Grupo PDI',
            'descricao'    => null,
            'data_inicio'  => now()->format('Y-m-d'),
            'data_fim'     => now()->addDays(60)->format('Y-m-d'),
            'colaboradores'=> [$this->colab1->id, $this->colab2->id], // colab2 é novo
            'acoes'        => [
                ['titulo' => 'Ação 1', 'descricao' => '', 'original_titulo' => 'Ação 1'],
            ],
        ];

        $this->actingAs($this->master)
             ->put(route('pdi-grupos.update', $grupo->id), $payload);

        // colab2 (novo) deve ter recebido atividade
        $this->assertDatabaseHas('atividades', [
            'user_id'    => $this->colab2->id,
            'empresa_id' => $this->empresa->id,
        ]);

        // colab1 (já existia) NÃO deve ter recebido nova atividade
        $this->assertDatabaseMissing('atividades', [
            'user_id' => $this->colab1->id,
        ]);
    }
}
