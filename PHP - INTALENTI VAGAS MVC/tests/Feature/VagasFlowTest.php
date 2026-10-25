<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Vaga;
use App\Models\PerguntaVaga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VagasFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_view_vagas_list(): void
    {
        $response = $this->get('/vagas');
        $response->assertStatus(200);
        $response->assertSee('Desenvolvedor Full Stack PHP');
    }

    public function test_candidate_registration_creates_profile(): void
    {
        $response = $this->post('/registro', [
            'tipo_usuario' => 1,
            'name' => 'Ana Clara Santos',
            'email' => 'anaclara@teste.com',
            'cpf' => '333.444.555-66',
            'celular' => '(44) 99111-2233',
            'data_nascimento' => '1998-05-20',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/curriculo');
        $this->assertDatabaseHas('users', ['email' => 'anaclara@teste.com']);
        $this->assertDatabaseHas('candidatos', ['nome_completo' => 'Ana Clara Santos']);
    }

    public function test_cep_api_returns_json(): void
    {
        $response = $this->get('/api/cep/87020000');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cep', 'cidade', 'estado'
        ]);
    }

    public function test_eliminatory_question_blocks_application_with_message(): void
    {
        $user = User::where('tipo_usuario', 1)->first();
        $vaga = Vaga::first();

        $perguntaEliminatoria = $vaga->perguntas()->where('eliminatoria', true)->first();

        $response = $this->actingAs($user)->post("/vagas/{$vaga->id}/aplicar", [
            'respostas' => [
                $perguntaEliminatoria->id => 'Não',
            ],
        ]);

        $response->assertSessionHas('alerta_eliminatoria');
        $this->assertDatabaseMissing('candidaturas', [
            'vaga_id' => $vaga->id,
            'candidato_id' => $user->candidato->id,
        ]);
    }

    public function test_creating_vaga_sanitizes_salary_values_properly(): void
    {
        $empresaUser = User::where('tipo_usuario', 2)->first();

        $response = $this->actingAs($empresaUser)->post('/vagas', [
            'titulo' => 'Suporte Técnico N1',
            'tipo_contrato' => 'PJ',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Atendimento a clientes e resolução de chamados.',
            'faixa_salarial_min' => '2000.00',
            'faixa_salarial_max' => '2800.00',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('vagas', [
            'titulo' => 'Suporte Técnico N1',
            'faixa_salarial_min' => 2000.00,
            'faixa_salarial_max' => 2800.00,
        ]);
    }

    public function test_candidate_cannot_start_chat_before_recruiter(): void
    {
        $vaga = Vaga::first();
        $candidato = \App\Models\Candidato::first();
        $candidateUser = $candidato->user;
        $recruiter = $vaga->recrutador;

        $candidatura = \App\Models\Candidatura::create([
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

        // Candidato tenta enviar mensagem antes da empresa -> bloqueado com 403
        $response = $this->actingAs($candidateUser)
            ->postJson("/chat/{$chat->id}/enviar", ['mensagem' => 'Olá empresa!']);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('mensagens_chat', ['mensagem' => 'Olá empresa!']);

        // Recrutador envia a primeira mensagem -> sucesso
        $recruiterResponse = $this->actingAs($recruiter)
            ->postJson("/chat/{$chat->id}/enviar", ['mensagem' => 'Olá candidato!']);

        $recruiterResponse->assertStatus(200);
        $this->assertDatabaseHas('mensagens_chat', ['mensagem' => 'Olá candidato!']);

        // Agora o candidato pode responder normalmente
        $replyResponse = $this->actingAs($candidateUser)
            ->postJson("/chat/{$chat->id}/enviar", ['mensagem' => 'Olá, obrigado pelo contato!']);

        $replyResponse->assertStatus(200);
        $this->assertDatabaseHas('mensagens_chat', ['mensagem' => 'Olá, obrigado pelo contato!']);
    }

    public function test_recruiter_can_view_candidate_profile_and_update_status(): void
    {
        $vaga = Vaga::first();
        $candidato = \App\Models\Candidato::first();
        $candidateUser = $candidato->user;
        $recruiter = $vaga->recrutador;

        $candidatura = \App\Models\Candidatura::create([
            'vaga_id' => $vaga->id,
            'candidato_id' => $candidato->id,
            'status' => 'pendente',
            'data_candidatura' => now(),
        ]);

        // 1. Recrutador acessa a lista de candidatos com ordenação por proximidade
        $listResponse = $this->actingAs($recruiter)->get("/vagas/{$vaga->id}/candidatos?ordem=proximos");
        $listResponse->assertStatus(200);
        $listResponse->assertSee($candidato->nome_completo);

        // 2. Recrutador acessa o perfil do candidato específico
        $profileResponse = $this->actingAs($recruiter)->get("/candidato/perfil/{$candidateUser->id}?vaga_id={$vaga->id}");
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee($candidato->nome_completo);
        $profileResponse->assertSee('Ações do Recrutador');

        // 3. Recrutador altera o status para 'em_processo'
        $statusResponse = $this->actingAs($recruiter)->post("/candidaturas/{$candidatura->id}/status", [
            'status' => 'em_processo',
        ]);

        $statusResponse->assertSessionHas('success');
        $this->assertDatabaseHas('candidaturas', [
            'id' => $candidatura->id,
            'status' => 'em_processo',
        ]);

        // 4. Candidato recebeu a notificação
        $this->assertDatabaseHas('notificacoes', [
            'user_id' => $candidateUser->id,
            'titulo' => 'Status da Candidatura Atualizado',
        ]);
    }

    public function test_registration_rejects_duplicate_cpf(): void
    {
        $existing = User::whereNotNull('cpf')->first();

        $response = $this->post('/registro', [
            'tipo_usuario' => 1,
            'name' => 'Novo Candidato Mesma Id',
            'email' => 'novo_cpf@teste.com',
            'cpf' => $existing->cpf, // mesmo CPF
            'celular' => '(44) 99111-2233',
            'data_nascimento' => '1995-01-01',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('cpf');
        $this->assertDatabaseMissing('users', ['email' => 'novo_cpf@teste.com']);
    }

    public function test_registration_rejects_duplicate_cnpj(): void
    {
        $existingEmpresa = Empresa::where('tipo', 'empresa')->first();

        $response = $this->post('/registro', [
            'tipo_usuario' => 2,
            'name' => 'Recrutador Teste',
            'email' => 'novo_cnpj@teste.com',
            'nome_empresa' => 'Empresa Duplicada Ltda',
            'cnpj' => $existingEmpresa->cnpj, // mesmo CNPJ
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('cnpj');
        $this->assertDatabaseMissing('users', ['email' => 'novo_cnpj@teste.com']);
    }

    public function test_empresa_only_sees_its_own_vacancies_on_index(): void
    {
        $empresa1 = Empresa::where('tipo', 'empresa')->first();
        $userEmpresa1 = $empresa1->usuarios()->first();

        // Cria uma segunda empresa com uma vaga diferente
        $empresa2 = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Outra Empresa Isolada Ltda',
            'tipo' => 'empresa',
            'cnpj' => '99888777000166',
            'cidade' => 'Londrina',
            'estado' => 'PR',
            'status' => true,
        ]);

        $vagaEmpresa2 = Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresa2->id,
            'recrutador_id' => $userEmpresa1->id,
            'titulo' => 'Vaga Exclusiva da Empresa 2',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Londrina, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição da vaga exclusiva da empresa 2',
            'status' => 'ativa',
        ]);

        // Acessa /vagas como a Empresa 1
        $response = $this->actingAs($userEmpresa1)->get('/vagas');
        $response->assertStatus(200);

        // Deve ver as vagas da Empresa 1
        $response->assertSee('Desenvolvedor Full Stack PHP');

        // NÃO deve ver a vaga da Empresa 2
        $response->assertDontSee('Vaga Exclusiva da Empresa 2');
    }

    public function test_candidato_sees_all_active_vacancies_on_index(): void
    {
        $candidatoUser = User::where('tipo_usuario', 1)->first();

        // Cria vaga em outra empresa
        $empresa2 = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa Nova Aberta',
            'tipo' => 'empresa',
            'cnpj' => '88777666000155',
            'cidade' => 'Curitiba',
            'estado' => 'PR',
            'status' => true,
        ]);

        Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresa2->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Vaga Aberta em Curitiba',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Curitiba, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição Curitiba',
            'status' => 'ativa',
        ]);

        // Candidato acessa /vagas
        $response = $this->actingAs($candidatoUser)->get('/vagas');
        $response->assertStatus(200);

        // Candidato vê todas as vagas ativas de ambas as empresas
        $response->assertSee('Desenvolvedor Full Stack PHP');
        $response->assertSee('Vaga Aberta em Curitiba');
    }

    public function test_prefeitura_only_sees_vacancies_of_its_city_on_index(): void
    {
        $prefeituraUser = User::where('tipo_usuario', 3)->first();

        // Vaga em Maringá (cidade da prefeitura)
        Vaga::create([
            'portal_id' => 1,
            'empresa_id' => Empresa::first()->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Vaga Local Maringá',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Maringá, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição Maringá',
            'status' => 'ativa',
        ]);

        // Vaga em outra cidade (Cascavel)
        $empresaCascavel = Empresa::create([
            'portal_id' => 1,
            'nome_empresa' => 'Empresa Cascavel',
            'tipo' => 'empresa',
            'cnpj' => '77666555000144',
            'cidade' => 'Cascavel',
            'estado' => 'PR',
            'status' => true,
        ]);

        Vaga::create([
            'portal_id' => 1,
            'empresa_id' => $empresaCascavel->id,
            'recrutador_id' => User::where('tipo_usuario', 2)->first()->id,
            'titulo' => 'Vaga Distante Cascavel',
            'tipo_contrato' => 'CLT',
            'modalidade' => 'Presencial',
            'localizacao' => 'Cascavel, PR',
            'ramo_atividade' => 'Tecnologia, TI, Informática',
            'descricao' => 'Descrição Cascavel',
            'status' => 'ativa',
        ]);

        // Prefeitura de Maringá acessa /vagas
        $response = $this->actingAs($prefeituraUser)->get('/vagas');
        $response->assertStatus(200);

        // Vê a vaga de Maringá
        $response->assertSee('Vaga Local Maringá');

        // NÃO vê a vaga de Cascavel
        $response->assertDontSee('Vaga Distante Cascavel');
    }

    public function test_empresa_cannot_view_candidate_profile_not_applied_to_its_vacancies(): void
    {
        $recruiter = User::where('tipo_usuario', 2)->first();

        // Candidato que NÃO se candidatou a nenhuma vaga desta empresa
        $outroUser = User::create([
            'name' => 'Candidato Desconhecido',
            'email' => 'desconhecido@teste.com',
            'password' => bcrypt('password'),
            'cpf' => '99988877766',
            'tipo_usuario' => 1,
            'portal_id' => 1,
            'status' => true,
        ]);

        $outroCandidato = Candidato::create([
            'user_id' => $outroUser->id,
            'nome_completo' => 'Candidato Desconhecido',
            'nacionalidade' => 'Brasileiro(a)',
        ]);

        // Tentativa de acesso sem candidatura correspondente
        $response = $this->actingAs($recruiter)->get("/candidato/perfil/{$outroUser->id}");
        $response->assertStatus(403);
    }
}

