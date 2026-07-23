<?php

namespace Database\Seeders;

use App\Models\Competencia;
use App\Models\Skill;
use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = 1; // TalentiPro Corp

        // Competência 1: Comunicação
        $comunicacao = Competencia::create([
            'empresa_id' => $empresaId,
            'nome' => 'Comunicação',
            'descricao' => 'Habilidade de se expressar de forma clara e objetiva',
        ]);

        $skillsComunicacao = [
            ['nome' => 'Clareza na Comunicação', 'descricao' => 'Expressa ideias de forma clara'],
            ['nome' => 'Escuta Ativa', 'descricao' => 'Ouve atentamente os outros'],
            ['nome' => 'Comunicação Escrita', 'descricao' => 'Escreve de forma profissional'],
        ];

        foreach ($skillsComunicacao as $skill) {
            Skill::create(array_merge($skill, ['competencia_id' => $comunicacao->id]));
        }

        // Competência 2: Trabalho em Equipe
        $trabalhoEquipe = Competencia::create([
            'empresa_id' => $empresaId,
            'nome' => 'Trabalho em Equipe',
            'descricao' => 'Capacidade de colaborar efetivamente com colegas',
        ]);

        $skillsEquipe = [
            ['nome' => 'Colaboração', 'descricao' => 'Trabalha bem com outros'],
            ['nome' => 'Resolução de Conflitos', 'descricao' => 'Resolve desentendimentos de forma construtiva'],
            ['nome' => 'Compartilhamento de Conhecimento', 'descricao' => 'Compartilha aprendizados com a equipe'],
        ];

        foreach ($skillsEquipe as $skill) {
            Skill::create(array_merge($skill, ['competencia_id' => $trabalhoEquipe->id]));
        }

        // Competência 3: Liderança
        $lideranca = Competencia::create([
            'empresa_id' => $empresaId,
            'nome' => 'Liderança',
            'descricao' => 'Capacidade de inspirar e guiar equipes',
        ]);

        $skillsLideranca = [
            ['nome' => 'Tomada de Decisão', 'descricao' => 'Toma decisões de forma assertiva'],
            ['nome' => 'Delegação', 'descricao' => 'Delega tarefas apropriadamente'],
            ['nome' => 'Motivação de Equipe', 'descricao' => 'Mantém a equipe motivada'],
            ['nome' => 'Feedback Construtivo', 'descricao' => 'Fornece feedback de qualidade'],
        ];

        foreach ($skillsLideranca as $skill) {
            Skill::create(array_merge($skill, ['competencia_id' => $lideranca->id]));
        }

        // Competência 4: Orientação para Resultados
        $resultados = Competencia::create([
            'empresa_id' => $empresaId,
            'nome' => 'Orientação para Resultados',
            'descricao' => 'Foco em atingir metas e objetivos',
        ]);

        $skillsResultados = [
            ['nome' => 'Cumprimento de Prazos', 'descricao' => 'Entrega tarefas no prazo'],
            ['nome' => 'Qualidade do Trabalho', 'descricao' => 'Mantém alto padrão de qualidade'],
            ['nome' => 'Produtividade', 'descricao' => 'Produz de forma eficiente'],
        ];

        foreach ($skillsResultados as $skill) {
            Skill::create(array_merge($skill, ['competencia_id' => $resultados->id]));
        }

        // Competência 5: Inovação e Criatividade
        $inovacao = Competencia::create([
            'empresa_id' => $empresaId,
            'nome' => 'Inovação e Criatividade',
            'descricao' => 'Capacidade de propor soluções criativas',
        ]);

        $skillsInovacao = [
            ['nome' => 'Pensamento Criativo', 'descricao' => 'Propõe ideias inovadoras'],
            ['nome' => 'Resolução de Problemas', 'descricao' => 'Encontra soluções para desafios'],
            ['nome' => 'Adaptabilidade', 'descricao' => 'Adapta-se a mudanças facilmente'],
        ];

        foreach ($skillsInovacao as $skill) {
            Skill::create(array_merge($skill, ['competencia_id' => $inovacao->id]));
        }

        // Vincular competências aos cargos
        // Todos os cargos DA EMPRESA avaliam Comunicação e Trabalho em Equipe
        $todosCargos = Cargo::where('empresa_id', $empresaId)->get();
        foreach ($todosCargos as $cargo) {
            $cargo->competencias()->attach([$comunicacao->id, $trabalhoEquipe->id, $resultados->id]);
        }

        // Cargos de liderança avaliam Liderança
        // Filtramos por ID, mas numa lógica real seria por atributo 'is_lider' ou nome
        // Como o ID é auto-incremento e reinicia no refresh, pode dar certo, mas idealmente buscaria por nome.
        // Vamos manter por ID mas sabendo que é do seed.
        // Mas como vamos resetar, os IDs serão 1..N.
        // Mas espera! Se criarmos seeds para OUTRA empresa, os IDs da outra empresa serão maiores.
        // O seed atual SÓ cria da empresa 1. Então IDs coincidem.

        $cargosLideranca = Cargo::where('empresa_id', $empresaId)
            ->whereIn('nome', [
                'Gerente de RH',
                'Gerente de TI',
                'Tech Lead',
                'Gerente Financeiro',
                'Gerente Comercial',
                'Gerente de Marketing',
                'Gerente de Operações'
            ])->get();

        foreach ($cargosLideranca as $cargo) {
            $cargo->competencias()->attach($lideranca->id);
        }

        // Cargos de TI avaliam Inovação
        // Buscar departamento TI primeiro para ser mais seguro
        $deptTI = \App\Models\Departamento::where('empresa_id', $empresaId)->where('nome', 'Tecnologia da Informação')->first();
        if ($deptTI) {
            $cargosTI = Cargo::where('empresa_id', $empresaId)->where('departamento_id', $deptTI->id)->get();
            foreach ($cargosTI as $cargo) {
                $cargo->competencias()->attach($inovacao->id);
            }
        }
    }
}
