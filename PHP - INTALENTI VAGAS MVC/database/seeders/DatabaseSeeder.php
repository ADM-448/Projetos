<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Vaga;
use App\Models\PerguntaVaga;
use App\Models\ExperienciaProfissional;
use App\Models\FormacaoAcademica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Portal Prefeitura (Multi-Tenancy)
        $prefeitura = Empresa::firstOrCreate(
            ['id' => 1],
            [
                'portal_id' => null,
                'nome_empresa' => 'Município de Maringá',
                'nome_fantasia' => 'Prefeitura de Maringá - Observatório',
                'cnpj' => '76282656000106',
                'tipo' => 'prefeitura',
                'dominio' => 'localhost',
                'cidade' => 'Maringá',
                'estado' => 'PR',
                'status' => true,
            ]
        );

        // 2. Usuário Administrador Geral
        $admin = User::firstOrCreate(
            ['email' => 'admin@intalenti.com.br'],
            [
                'name' => 'Administrador Geral',
                'password' => Hash::make('password'),
                'cpf' => '00000000000',
                'tipo_usuario' => 3, // Admin
                'portal_id' => 1,
                'status' => true,
            ]
        );

        // 3. Empresa Parceira de RH
        $empresa = Empresa::firstOrCreate(
            ['cnpj' => '12345678000190'],
            [
                'portal_id' => 1,
                'nome_empresa' => 'Tech Solutions Brasil Ltda',
                'nome_fantasia' => 'Tech Solutions',
                'tipo' => 'empresa',
                'dominio' => null,
                'cidade' => 'Maringá',
                'estado' => 'PR',
                'status' => true,
            ]
        );

        // 4. Usuário Recrutador (RH da Empresa)
        $recrutador = User::firstOrCreate(
            ['email' => 'pamela@gmail.com'],
            [
                'name' => 'Pamela (RH)',
                'password' => Hash::make('12345678'),
                'cpf' => '11122233344',
                'tipo_usuario' => 2, // Empresa/RH
                'portal_id' => 1,
                'status' => true,
            ]
        );

        // Vínculo Recrutador -> Empresa
        $empresa->usuarios()->syncWithoutDetaching([
            $recrutador->id => ['role' => 'admin', 'status' => true],
        ]);

        // 5. Usuário Candidato
        $userCandidato = User::firstOrCreate(
            ['email' => 'candidato@intalenti.com.br'],
            [
                'name' => 'Carlos Alberto Silva',
                'password' => Hash::make('password'),
                'cpf' => '22233344455',
                'tipo_usuario' => 1, // Candidato
                'portal_id' => 1,
                'status' => true,
            ]
        );

        // Perfil do Candidato
        $candidato = Candidato::firstOrCreate(
            ['user_id' => $userCandidato->id],
            [
                'nome_completo' => 'Carlos Alberto Silva',
                'data_nascimento' => '1995-06-15',
                'telefone' => '(44) 3031-1000',
                'celular' => '(44) 99888-7766',
                'whatsapp' => '(44) 99888-7766',
                'cep' => '87020-000',
                'logradouro' => 'Avenida Brasil',
                'bairro' => 'Zona 01',
                'cidade' => 'Maringá',
                'estado' => 'PR',
                'latitude' => -23.420999,
                'longitude' => -51.933056,
                'sobre_mim' => 'Profissional dedicado com 5 anos de experiência em desenvolvimento web e sistemas corporativos.',
                'resumo_profissional' => 'Especialista em PHP, Laravel, MySQL e arquitetura RESTful.',
                'situacao_profissional' => 'Empregado procurando recolocação',
                'cargo_pretendido' => 'Desenvolvedor PHP / Full Stack',
                'faixa_salarial' => 'R$ 5.000 a R$ 7.000',
                'escolaridade' => 'Superior Completo',
                'sexo' => 'Masculino',
                'estado_civil' => 'Solteiro(a)',
                'nacionalidade' => 'Brasileiro(a)',
                'idiomas' => [
                    ['idioma' => 'Inglês', 'nivel' => 'Avançado'],
                    ['idioma' => 'Espanhol', 'nivel' => 'Intermediário'],
                ],
                'carteira_habilitacao' => 'B',
            ]
        );

        // Experiência e Formação do Candidato
        ExperienciaProfissional::firstOrCreate(
            ['candidato_id' => $candidato->id, 'empresa' => 'Agência Inova Web'],
            [
                'cargo' => 'Desenvolvedor PHP Pleno',
                'data_inicio' => '2021-02-01',
                'atual' => true,
                'descricao' => 'Desenvolvimento e manutenção de plataformas web com Laravel e APIs REST.',
            ]
        );

        FormacaoAcademica::firstOrCreate(
            ['candidato_id' => $candidato->id, 'curso' => 'Análise e Desenvolvimento de Sistemas'],
            [
                'instituicao' => 'Universidade Estadual de Maringá',
                'nivel' => 'Graduação',
                'data_inicio' => '2016-02-01',
                'data_fim' => '2019-12-15',
                'concluido' => true,
            ]
        );

        // 6. Vagas Iniciais
        $vaga1 = Vaga::withoutGlobalScopes()->firstOrCreate(
            ['titulo' => 'Desenvolvedor Full Stack PHP / Laravel', 'empresa_id' => $empresa->id],
            [
                'recrutador_id' => $recrutador->id,
                'portal_id' => 1,
                'tipo_contrato' => 'CLT',
                'modalidade' => 'Híbrido',
                'localizacao' => 'Maringá, PR',
                'cep' => '87020-000',
                'ramo_atividade' => 'Tecnologia, TI, Informática',
                'descricao' => 'Buscamos desenvolvedor Full Stack para atuar na evolução de nossa plataforma de serviços com Laravel e Vue.js.',
                'requisitos' => "• Experiência sólida em PHP 8+ e Laravel;\n• Conhecimento em banco de dados MySQL e indexação;\n• Experiência com Git e boas práticas de código limpo.",
                'beneficios' => "• Vale Alimentação/Refeição (R$ 900/mês);\n• Plano de Saúde e Odontológico;\n• Auxílio Home Office;\n• Horário flexível.",
                'confidencial' => false,
                'tipo_vaga' => 'Efetivo',
                'experiencia' => 'Pleno',
                'faixa_salarial_min' => 5000.00,
                'faixa_salarial_max' => 7500.00,
                'salario_a_combinar' => false,
                'latitude' => -23.420999,
                'longitude' => -51.933056,
                'status' => 'ativa',
            ]
        );

        PerguntaVaga::firstOrCreate(
            ['vaga_id' => $vaga1->id, 'texto_pergunta' => 'Você possui mais de 2 anos de experiência com Laravel?'],
            ['eliminatoria' => true]
        );

        PerguntaVaga::firstOrCreate(
            ['vaga_id' => $vaga1->id, 'texto_pergunta' => 'Você reside ou tem disponibilidade para atuar presencialmente 2x na semana em Maringá?'],
            ['eliminatoria' => true]
        );

        $vaga2 = Vaga::withoutGlobalScopes()->firstOrCreate(
            ['titulo' => 'Analista de Suporte e Infraestrutura TI', 'empresa_id' => $empresa->id],
            [
                'recrutador_id' => $recrutador->id,
                'portal_id' => 1,
                'tipo_contrato' => 'CLT',
                'modalidade' => 'Presencial',
                'localizacao' => 'Maringá, PR',
                'cep' => '87020-000',
                'ramo_atividade' => 'Tecnologia, TI, Informática',
                'descricao' => 'Atendimento a chamados, suporte a redes corporativas, servidores Windows/Linux e equipamentos de usuários.',
                'requisitos' => "• Conhecimento em redes (TCP/IP, DNS, DHCP);\n• Atendimento N1/N2;\n• Proatividade e boa comunicação.",
                'beneficios' => "• Vale Refeição;\n• Vale Transporte;\n• Seguro de Vida.",
                'confidencial' => false,
                'tipo_vaga' => 'Efetivo',
                'experiencia' => 'Júnior',
                'faixa_salarial_min' => 2800.00,
                'faixa_salarial_max' => 3500.00,
                'salario_a_combinar' => false,
                'latitude' => -23.420999,
                'longitude' => -51.933056,
                'status' => 'ativa',
            ]
        );
    }
}
