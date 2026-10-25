<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Vaga;
use App\Models\Candidatura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class SecurityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Valida que o banco de dados rejeita inserção direta de CPFs duplicados.
     */
    public function test_database_enforces_unique_cpf_constraint(): void
    {
        $existing = User::whereNotNull('cpf')->first();

        $this->expectException(QueryException::class);

        User::create([
            'name' => 'Tentativa Burlar Duplicata',
            'email' => 'burlar_cpf@teste.com',
            'password' => bcrypt('123456'),
            'cpf' => $existing->cpf,
            'tipo_usuario' => 1,
            'portal_id' => 1,
            'status' => true,
        ]);
    }

    /**
     * Valida que o banco de dados rejeita inserção direta de CNPJs duplicados.
     */
    public function test_database_enforces_unique_cnpj_constraint(): void
    {
        $existingEmpresa = Empresa::whereNotNull('cnpj')->first();

        $this->expectException(QueryException::class);

        Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Tentativa Burlar CNPJ',
            'tipo' => 'empresa',
            'cnpj' => $existingEmpresa->cnpj,
            'cidade' => 'Maringá',
            'estado' => 'PR',
            'status' => true,
        ]);
    }

    /**
     * Valida que o banco aceita múltiplos registros com CPF ou CNPJ nulos sem conflito de UNIQUE.
     */
    public function test_database_allows_multiple_null_values(): void
    {
        $user1 = User::create([
            'name' => 'Estrangeiro 1',
            'email' => 'estrangeiro1@teste.com',
            'password' => bcrypt('123456'),
            'cpf' => null,
            'tipo_usuario' => 1,
            'portal_id' => 1,
            'status' => true,
        ]);

        $user2 = User::create([
            'name' => 'Estrangeiro 2',
            'email' => 'estrangeiro2@teste.com',
            'password' => bcrypt('123456'),
            'cpf' => null,
            'tipo_usuario' => 1,
            'portal_id' => 1,
            'status' => true,
        ]);

        $this->assertDatabaseHas('users', ['id' => $user1->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    /**
     * Valida que uma empresa A NUNCA visualiza vagas da empresa B no catálogo de vagas.
     */
    public function test_company_isolation_on_vacancies_index(): void
    {
        $empresaA = Empresa::where('tipo', 'empresa')->first();
        $recrutadorA = $empresaA->usuarios()->first();

        $empresaB = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa Concorrente Beta S/A',
            'tipo' => 'empresa',
            'cnpj' => '99888777000166',
            'cidade' => 'Londrina',
            'estado' => 'PR',
            'status' => true,
        ]);

        $vagaB = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaB->id,
            'recrutador_id' => $recrutadorA->id,
            'titulo' => 'Vaga Ultra Secreta Empresa B',
            'tipo_contrato' => 'PJ',
            'modalidade' => 'Presencial',
            'localizacao' => 'Londrina, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Dados confidenciais da empresa B.',
            'status' => 'ativa',
        ]);

        // Recrutador da Empresa A acessa /vagas
        $response = $this->actingAs($recrutadorA)->get('/vagas');
        $response->assertStatus(200);

        // Vê as vagas da Empresa A
        $response->assertSee('Desenvolvedor Full Stack PHP');

        // NÃO pode ver a vaga da Empresa B
        $response->assertDontSee('Vaga Ultra Secreta Empresa B');
        $response->assertDontSee('Empresa Concorrente Beta S/A');
    }

    /**
     * Valida que uma empresa não pode editar ou alterar vagas de outra empresa (retorna 403).
     */
    public function test_company_cannot_edit_or_update_other_company_vacancy(): void
    {
        $empresaA = Empresa::where('tipo', 'empresa')->first();
        $recrutadorA = $empresaA->usuarios()->first();

        $recrutadorB = User::create([
            'name' => 'Recrutador B',
            'email' => 'rh_b@teste.com',
            'password' => bcrypt('123456'),
            'tipo_usuario' => 2,
            'portal_id' => 1,
            'status' => true,
        ]);

        $empresaB = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa B',
            'tipo' => 'empresa',
            'cnpj' => '88777666000155',
            'cidade' => 'Maringá',
            'estado' => 'PR',
            'status' => true,
        ]);

        $empresaB->usuarios()->attach($recrutadorB->id, ['role' => 'admin', 'status' => true]);

        $vagaB = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaB->id,
            'recrutador_id' => $recrutadorB->id,
            'titulo' => 'Vaga Legítima da Empresa B',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição',
            'status' => 'ativa',
        ]);

        // Empresa A tenta acessar a tela de edição da vaga da Empresa B -> 403
        $editResponse = $this->actingAs($recrutadorA)->get("/vagas/{$vagaB->id}/editar");
        $editResponse->assertStatus(403);

        // Empresa A tenta submeter alteração na vaga da Empresa B -> 403
        $updateResponse = $this->actingAs($recrutadorA)->put("/vagas/{$vagaB->id}", [
            'titulo' => 'Hacked Title',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Nova descrição',
        ]);
        $updateResponse->assertStatus(403);
    }

    /**
     * Valida que uma empresa não pode ver nem alterar candidaturas de vagas de outras empresas.
     */
    public function test_company_cannot_access_or_screen_candidacies_of_other_companies(): void
    {
        $empresaA = Empresa::where('tipo', 'empresa')->first();
        $recrutadorA = $empresaA->usuarios()->first();

        $empresaB = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa B Candidaturas',
            'tipo' => 'empresa',
            'cnpj' => '77666555000144',
            'cidade' => 'Maringá',
            'estado' => 'PR',
            'status' => true,
        ]);

        $vagaB = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaB->id,
            'recrutador_id' => $recrutadorA->id,
            'titulo' => 'Vaga Protegida B',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição',
            'status' => 'ativa',
        ]);

        $candidato = Candidato::first();

        $candidaturaB = Candidatura::create([
            'vaga_id' => $vagaB->id,
            'candidato_id' => $candidato->id,
            'status' => 'pendente',
            'data_candidatura' => now(),
        ]);

        // Recrutador A tenta acessar a triagem de candidatos da vaga B -> 403
        $triagemResponse = $this->actingAs($recrutadorA)->get("/vagas/{$vagaB->id}/candidatos");
        $triagemResponse->assertStatus(403);

        // Recrutador A tenta alterar o status da candidatura na vaga B -> 403
        $statusResponse = $this->actingAs($recrutadorA)->post("/candidaturas/{$candidaturaB->id}/status", [
            'status' => 'aprovado',
        ]);
        $statusResponse->assertStatus(403);

        // O status no banco permanece pendente
        $this->assertDatabaseHas('candidaturas', [
            'id' => $candidaturaB->id,
            'status' => 'pendente',
        ]);
    }

    /**
     * Valida que a prefeitura tem isolamento completo por município tanto em /vagas quanto no Observatório.
     */
    public function test_prefecture_isolation_by_municipality(): void
    {
        $prefeituraUser = User::where('tipo_usuario', 3)->first();

        // 1. Cria vaga de R$ 5.000 em Maringá (da cidade da prefeitura)
        $vagaMaringa = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => Empresa::first()->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Vaga Municipal Maringá Tech',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição',
            'faixa_salarial_min' => 5000.00,
            'status' => 'ativa',
        ]);

        // 2. Cria vaga de R$ 9.000 em Curitiba (fora da cidade da prefeitura)
        $empresaCuritiba = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa Curitibana',
            'tipo' => 'empresa',
            'cnpj' => '66555444000133',
            'cidade' => 'Curitiba',
            'estado' => 'PR',
            'status' => true,
        ]);

        $vagaCuritiba = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaCuritiba->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Vaga Remota Externa Curitiba',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Curitiba, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição',
            'faixa_salarial_min' => 9000.00,
            'status' => 'ativa',
        ]);

        // Prefeitura de Maringá acessa /vagas
        $vagasResponse = $this->actingAs($prefeituraUser)->get('/vagas');
        $vagasResponse->assertStatus(200);
        $vagasResponse->assertSee('Vaga Municipal Maringá Tech');
        $vagasResponse->assertDontSee('Vaga Remota Externa Curitiba');

        // Prefeitura de Maringá acessa o Observatório (/prefeitura/dashboard)
        $dashboardResponse = $this->actingAs($prefeituraUser)->get('/prefeitura/dashboard');
        $dashboardResponse->assertStatus(200);

        // Os KPIs no dashboard não incluem as empresas nem as vagas de Curitiba
        $kpis = $dashboardResponse->viewData('kpis');
        $this->assertNotNull($kpis);
        // O valor salarial da vaga de Curitiba (9000) NÃO deve estar somado no impacto econômico de Maringá
        $this->assertLessThan(9000.00, $kpis['impacto_economico'] - 5000.00 + 0.01);
    }

    /**
     * Valida que o candidato livre tem acesso a visualizar todas as vagas ativas de qualquer empresa/cidade.
     */
    public function test_candidate_sees_all_active_vacancies(): void
    {
        $candidatoUser = User::where('tipo_usuario', 1)->first();

        $empresaOutra = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Multinacional Florianópolis',
            'tipo' => 'empresa',
            'cnpj' => '55444333000122',
            'cidade' => 'Florianópolis',
            'estado' => 'SC',
            'status' => true,
        ]);

        Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaOutra->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Tech Lead Floripa',
            'tipo_contrato' => 'PJ',
            'modalidade' => 'Remoto',
            'localizacao' => 'Florianópolis, SC',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição ampla para candidatos',
            'status' => 'ativa',
        ]);

        $response = $this->actingAs($candidatoUser)->get('/vagas');
        $response->assertStatus(200);

        // Candidato enxerga a vaga local e a vaga externa
        $response->assertSee('Desenvolvedor Full Stack PHP');
        $response->assertSee('Tech Lead Floripa');
    }

    /**
     * Valida que um candidato não consegue invadir telas restritas de empresas (/dashboard ou criação de vagas).
     */
    public function test_candidate_cannot_access_company_management_endpoints(): void
    {
        $candidatoUser = User::where('tipo_usuario', 1)->first();

        // Tentativa de acessar /dashboard (middleware empresa) -> redireciona com segurança para vagas.index com erro
        $dashboardResponse = $this->actingAs($candidatoUser)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('vagas.index'));
        $dashboardResponse->assertSessionHas('error');

        // Tentativa de acessar /vagas/nova (middleware role:empresa,admin) -> redireciona para vagas.index com erro
        $novaVagaResponse = $this->actingAs($candidatoUser)->get('/vagas/nova');
        $novaVagaResponse->assertRedirect(route('vagas.index'));
        $novaVagaResponse->assertSessionHas('error');
    }

    /**
     * Valida que mensagens enviadas no chat geram notificação automática para o interlocutor.
     */
    public function test_chat_message_creates_notification_for_interlocutor(): void
    {
        $vaga = Vaga::first();
        $candidato = Candidato::first();
        $candidateUser = $candidato->user;
        $recruiter = $vaga->recrutador;

        $candidatura = Candidatura::create([
            'vaga_id' => $vaga->id,
            'candidato_id' => $candidato->id,
            'status' => 'pendente',
            'data_candidatura' => now(),
        ]);

        $chat = \App\Models\Chat::create([
            'candidatura_id' => $candidatura->id,
            'vaga_id' => $vaga->id,
            'candidato_user_id' => $candidateUser->id,
            'recrutador_id' => $recruiter->id,
        ]);

        // 1. Recrutador envia mensagem para o candidato
        $this->actingAs($recruiter)->postJson("/chat/{$chat->id}/enviar", [
            'mensagem' => 'Olá! Gostaríamos de agendar uma entrevista técnica.',
        ]);

        // Candidato deve ter recebido notificação
        $this->assertDatabaseHas('notificacoes', [
            'user_id' => $candidateUser->id,
            'titulo' => 'Nova Mensagem no Chat',
            'lida' => false,
        ]);

        // 2. Candidato responde ao recrutador
        $this->actingAs($candidateUser)->postJson("/chat/{$chat->id}/enviar", [
            'mensagem' => 'Perfeito! Tenho disponibilidade amanhã às 14h.',
        ]);

        // Recrutador deve ter recebido notificação
        $this->assertDatabaseHas('notificacoes', [
            'user_id' => $recruiter->id,
            'titulo' => 'Nova Mensagem no Chat',
            'lida' => false,
        ]);
    }

    /**
     * Valida abertura de notificação no sininho (marca como lida e redireciona) e marcar todas como lidas.
     */
    public function test_notification_bell_can_mark_single_and_all_as_read(): void
    {
        $user = User::first();

        $notif1 = \App\Models\Notificacao::create([
            'user_id' => $user->id,
            'titulo' => 'Notificação de Teste 1',
            'mensagem' => 'Mensagem 1',
            'link' => route('vagas.index'),
            'lida' => false,
        ]);

        $notif2 = \App\Models\Notificacao::create([
            'user_id' => $user->id,
            'titulo' => 'Notificação de Teste 2',
            'mensagem' => 'Mensagem 2',
            'link' => route('vagas.index'),
            'lida' => false,
        ]);

        // 1. Clicar para abrir notificação 1 -> marca como lida e redireciona
        $openResponse = $this->actingAs($user)->get("/notificacoes/{$notif1->id}/abrir");
        $openResponse->assertRedirect(route('vagas.index'));
        $this->assertDatabaseHas('notificacoes', [
            'id' => $notif1->id,
            'lida' => true,
        ]);

        // 2. Notificação 2 ainda está não lida
        $this->assertDatabaseHas('notificacoes', [
            'id' => $notif2->id,
            'lida' => false,
        ]);

        // 3. Marcar todas como lidas via botão do sininho
        $readAllResponse = $this->actingAs($user)->post('/notificacoes/marcar-todas-lidas');
        $readAllResponse->assertSessionHas('success');

        // Agora todas estão marcadas como lidas
        $this->assertDatabaseHas('notificacoes', [
            'id' => $notif2->id,
            'lida' => true,
        ]);
    }

    /**
     * Valida que o candidato consegue ordenar vagas por proximidade em KM e filtrar por raio.
     */
    public function test_candidate_can_sort_vacancies_by_proximity_and_filter_by_radius(): void
    {
        $candidato = Candidato::first();
        $this->assertNotNull($candidato);
        $candidateUser = $candidato->user;
        $this->assertNotNull($candidateUser);

        // Define coordenadas para o candidato (ex: Centro de Maringá -23.4209, -51.9331)
        $candidato->update([
            'cep' => '87013-000',
            'cidade' => 'Maringá',
            'latitude' => -23.4209,
            'longitude' => -51.9331,
        ]);

        // Cria vaga próxima (Maringá ~ 2 km)
        $empresa = Empresa::first();
        $recruiterId = $empresa->usuarios()->first()->id ?? 1;

        $vagaPerto = Vaga::create([
            'empresa_id' => $empresa->id,
            'recrutador_id' => $recruiterId,
            'titulo' => 'Desenvolvedor PHP Perto de Casa',
            'ramo_atividade' => 'Tecnologia da Informação',
            'descricao' => 'Vaga pertinho',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'latitude' => -23.4150,
            'longitude' => -51.9300,
            'status' => 'ativa',
        ]);

        // Cria vaga distante (Curitiba ~ 400 km)
        $vagaLonge = Vaga::create([
            'empresa_id' => $empresa->id,
            'recrutador_id' => $recruiterId,
            'titulo' => 'Desenvolvedor PHP em Outra Cidade',
            'ramo_atividade' => 'Tecnologia da Informação',
            'descricao' => 'Vaga distante',
            'modalidade' => 'Presencial',
            'localizacao' => 'Curitiba, PR',
            'latitude' => -25.4284,
            'longitude' => -49.2733,
            'status' => 'ativa',
        ]);

        // 1. Acessa catálogo com ordem=proximos
        $response = $this->actingAs($candidateUser)->get(route('vagas.index', ['ordem' => 'proximos']));
        $response->assertStatus(200);
        $response->assertSee('Desenvolvedor PHP Perto de Casa');
        $response->assertSee('Próximas de Mim (KM)');
        $response->assertSee('km de você');

        // 2. Filtra por raio de 25 km -> Vaga perto aparece, vaga longe é excluída
        $responseRaio = $this->actingAs($candidateUser)->get(route('vagas.index', ['ordem' => 'proximos', 'raio_km' => 25]));
        $responseRaio->assertStatus(200);
        $responseRaio->assertSee('Desenvolvedor PHP Perto de Casa');
        $responseRaio->assertDontSee('Desenvolvedor PHP em Outra Cidade');
    }

    /**
     * Valida que o painel da empresa carrega o mapa de distribuição de candidatos com CEP aproximado.
     */
    public function test_company_dashboard_renders_candidate_distribution_map_with_approximate_cep(): void
    {
        $empresa = Empresa::where('tipo', 'empresa')->first();
        $recruiter = $empresa->usuarios()->first();
        $candidato = Candidato::first();

        // Candidato com CEP e localização
        $candidato->update([
            'cep' => '87047-010',
            'cidade' => 'Maringá',
            'bairro' => 'Jardim Alvorada',
            'latitude' => -23.4000,
            'longitude' => -51.9200,
        ]);

        // Cria vaga da empresa e candidatura
        $vaga = Vaga::create([
            'empresa_id' => $empresa->id,
            'recrutador_id' => $recruiter->id,
            'titulo' => 'Vaga de Teste para o Mapa',
            'ramo_atividade' => 'Tecnologia da Informação',
            'descricao' => 'Descrição',
            'status' => 'ativa',
        ]);

        Candidatura::create([
            'vaga_id' => $vaga->id,
            'candidato_id' => $candidato->id,
            'status' => 'pendente',
            'data_candidatura' => now(),
        ]);

        // Acessa o dashboard da empresa
        $response = $this->actingAs($recruiter)->get(route('dashboard'));
        $response->assertStatus(200);

        // Valida elementos do mapa
        $response->assertSee('mapaCandidatosEmpresa');
        $response->assertSee('Mapa de Distribuição de Candidatos');
        $response->assertSee('leaflet');
        $response->assertSee('87047-***'); // CEP com máscara aproximada (LGPD)
        $response->assertSee($candidato->nome_completo);
    }

    /**
     * Valida o fluxo de cadastro de uma nova prefeitura municipal (tipo 3) e o isolamento por cidade.
     */
    /**
     * Valida o fluxo de cadastro com status pendente, aprovação pelo Super Admin e configuração de domínio.
     */
    public function test_prefeitura_registration_flow_and_dashboard_isolation(): void
    {
        // 1. Cadastro da Prefeitura nasce pendente
        $response = $this->post(route('register'), [
            'tipo_usuario' => 3,
            'name' => 'Gestor Público de Sarandi',
            'email' => 'gestor.sarandi@teste.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'cnpj' => '99.888.777/0001-66',
            'nome_empresa' => 'Prefeitura Municipal de Sarandi',
            'cidade' => 'Sarandi',
            'estado' => 'PR',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'gestor.sarandi@teste.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->status); // Nasce pendente
        $this->assertTrue($user->isPrefeitura());
        $this->assertEquals('Sarandi', $user->getCidadePrefeitura());

        $empresa = Empresa::where('cnpj', '99888777000166')->first();
        $this->assertNotNull($empresa);
        $this->assertFalse($empresa->status); // Nasce inativa/pendente
        $this->assertEquals('prefeitura', $empresa->tipo);
        $this->assertEquals('Sarandi', $empresa->cidade);

        // 2. Tentativa de login antes da aprovação do Admin é bloqueada
        $loginBlocked = $this->post(route('login'), [
            'email' => 'gestor.sarandi@teste.com',
            'password' => 'senha123',
        ]);
        $loginBlocked->assertSessionHasErrors('email');
        $this->assertGuest();

        // 3. Super Admin acessa painel master e aprova a prefeitura
        $superAdmin = User::where('email', 'admin@intalenti.com.br')->first();
        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->isSuperAdmin());

        $adminDash = $this->actingAs($superAdmin)->get(route('admin.empresas'));
        $adminDash->assertStatus(200);
        $adminDash->assertSee('Prefeitura Municipal de Sarandi');
        $adminDash->assertSee('Pendente de Liberação');

        // Aprova / Ativa o acesso da prefeitura
        $approve = $this->actingAs($superAdmin)->post(route('admin.toggleStatus', $empresa->id));
        $approve->assertSessionHas('success');
        $this->assertTrue($empresa->fresh()->status);
        $this->assertTrue($user->fresh()->status);

        // Configura o domínio do portal
        $domainUpdate = $this->actingAs($superAdmin)->post(route('admin.salvarDominio', $empresa->id), [
            'dominio' => 'sarandi.localhost',
        ]);
        $domainUpdate->assertSessionHas('success');
        $this->assertEquals('sarandi.localhost', $empresa->fresh()->dominio);

        // 4. Agora o Gestor de Sarandi consegue logar e acessar o Observatório Municipal
        $this->actingAs($user->fresh());
        $dashResponse = $this->get(route('prefeitura.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Observatório do Trabalho Municipal');
    }

    /**
     * Valida o módulo de gestão de equipe da empresa: adicionar usuários, gerenciar funções, status e transferir vagas.
     */
    public function test_company_team_management_module(): void
    {
        $empresa = Empresa::where('tipo', 'empresa')->first();
        $adminUser = $empresa->usuarios()->first();
        // Garante role admin no pivot
        $empresa->usuarios()->updateExistingPivot($adminUser->id, ['role' => 'admin']);

        // 1. Acesso à listagem de equipe da empresa
        $response = $this->actingAs($adminUser)->get(route('empresa.equipe'));
        $response->assertStatus(200);
        $response->assertSee('Equipe e Recrutadores');
        $response->assertSee($empresa->nome_empresa);

        // Candidato comum não tem acesso
        $candidato = User::where('tipo_usuario', 1)->first();
        $candResponse = $this->actingAs($candidato)->get(route('empresa.equipe'));
        $candResponse->assertRedirect(route('vagas.index'));

        // 2. Adicionar novo colaborador (Recrutador / RH)
        $addResponse = $this->actingAs($adminUser)->post(route('empresa.equipe.adicionar'), [
            'name' => 'Carla Recrutadora',
            'email' => 'carla.rh@teste.com',
            'password' => 'senha1234',
            'role' => 'recrutador',
        ]);
        $addResponse->assertSessionHas('success');

        $novoUser = User::where('email', 'carla.rh@teste.com')->first();
        $this->assertNotNull($novoUser);
        $this->assertEquals('Carla Recrutadora', $novoUser->name);
        $this->assertEquals(2, $novoUser->tipo_usuario);

        // Verifica vínculo na empresa com role recrutador
        $this->assertDatabaseHas('empresa_usuarios', [
            'empresa_id' => $empresa->id,
            'user_id' => $novoUser->id,
            'role' => 'recrutador',
            'status' => true,
        ]);

        // Verifica notificação de boas-vindas
        $this->assertDatabaseHas('notificacoes', [
            'user_id' => $novoUser->id,
            'titulo' => 'Boas-vindas à equipe ' . $empresa->nome_empresa,
        ]);

        // 3. Promover colaboradora para Admin da empresa
        $promoResponse = $this->actingAs($adminUser)->post(route('empresa.equipe.funcao', $novoUser->id), [
            'role' => 'admin',
        ]);
        $promoResponse->assertSessionHas('success');
        $this->assertDatabaseHas('empresa_usuarios', [
            'empresa_id' => $empresa->id,
            'user_id' => $novoUser->id,
            'role' => 'admin',
        ]);

        // 4. Suspender e reativar acesso do colaborador
        $suspendResponse = $this->actingAs($adminUser)->post(route('empresa.equipe.status', $novoUser->id));
        $suspendResponse->assertSessionHas('success');
        $this->assertDatabaseHas('empresa_usuarios', [
            'empresa_id' => $empresa->id,
            'user_id' => $novoUser->id,
            'status' => false,
        ]);

        $reactivateResponse = $this->actingAs($adminUser)->post(route('empresa.equipe.status', $novoUser->id));
        $reactivateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('empresa_usuarios', [
            'empresa_id' => $empresa->id,
            'user_id' => $novoUser->id,
            'status' => true,
        ]);

        // 5. Colaboradora cria uma vaga e depois é removida com transferência para o admin
        $vagaCarla = Vaga::create([
            'empresa_id' => $empresa->id,
            'recrutador_id' => $novoUser->id,
            'titulo' => 'Vaga Criada por Carla',
            'ramo_atividade' => 'Tecnologia',
            'descricao' => 'Descrição',
            'status' => 'ativa',
        ]);

        $removeResponse = $this->actingAs($adminUser)->delete(route('empresa.equipe.remover', $novoUser->id), [
            'metodo' => 'transferir',
        ]);
        $removeResponse->assertSessionHas('success');

        // Carla não está mais na empresa
        $this->assertDatabaseMissing('empresa_usuarios', [
            'empresa_id' => $empresa->id,
            'user_id' => $novoUser->id,
        ]);

        // A vaga de Carla foi transferida para o admin
        $this->assertEquals($adminUser->id, $vagaCarla->fresh()->recrutador_id);
    }
}



