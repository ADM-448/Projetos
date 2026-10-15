<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscQuestion;
use App\Models\DiscOption;
use Illuminate\Support\Facades\DB;
use App\Enums\DiscProfileEnum;

class DiscSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 16 Blocos de Palavras Neutras (Sem viés óbvio) para avaliação DISC Profissional.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DiscOption::truncate();
        DiscQuestion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $blocos = [
            // Bloco 1
            [
                ['text' => 'Focado em Resultados', 'profile' => 'D'],
                ['text' => 'Comunicativo e Expressivo', 'profile' => 'I'],
                ['text' => 'Paciente e Ouvinte', 'profile' => 'S'],
                ['text' => 'Atento aos Detalhes', 'profile' => 'C'],
            ],
            // Bloco 2
            [
                ['text' => 'Toma Iniciativa Rápida', 'profile' => 'D'],
                ['text' => 'Gosta de Trabalhar em Equipe', 'profile' => 'I'],
                ['text' => 'Busca Harmonia no Ambiente', 'profile' => 'S'],
                ['text' => 'Segue Regras Estritamente', 'profile' => 'C'],
            ],
            // Bloco 3
            [
                ['text' => 'Competitivo por Natureza', 'profile' => 'D'],
                ['text' => 'Otimista e Entusiasta', 'profile' => 'I'],
                ['text' => 'Estável e Previsível', 'profile' => 'S'],
                ['text' => 'Analítico e Lógico', 'profile' => 'C'],
            ],
            // Bloco 4
            [
                ['text' => 'Direto ao Ponto', 'profile' => 'D'],
                ['text' => 'Persuasivo e Influente', 'profile' => 'I'],
                ['text' => 'Leal e Confiável', 'profile' => 'S'],
                ['text' => 'Exigente com a Qualidade', 'profile' => 'C'],
            ],
            // Bloco 5
            [
                ['text' => 'Assume Riscos Calculados', 'profile' => 'D'],
                ['text' => 'Gosta de Estar no Centro das Atenções', 'profile' => 'I'],
                ['text' => 'Prefere Rotinas Claras', 'profile' => 'S'],
                ['text' => 'Cauteloso Antes de Agir', 'profile' => 'C'],
            ],
            // Bloco 6
            [
                ['text' => 'Decidido Sob Pressão', 'profile' => 'D'],
                ['text' => 'Anima os Outros Facilmente', 'profile' => 'I'],
                ['text' => 'Acalma Situações Tensas', 'profile' => 'S'],
                ['text' => 'Foca em Fatos e Dados', 'profile' => 'C'],
            ],
            // Bloco 7
            [
                ['text' => 'Desafiador do Status Quo', 'profile' => 'D'],
                ['text' => 'Criativo em Soluções', 'profile' => 'I'],
                ['text' => 'Apoia os Colegas Constantemente', 'profile' => 'S'],
                ['text' => 'Metódico em Processos', 'profile' => 'C'],
            ],
            // Bloco 8
            [
                ['text' => 'Independente nas Ações', 'profile' => 'D'],
                ['text' => 'Sociável e Extrovertido', 'profile' => 'I'],
                ['text' => 'Tolerante e Compreensivo', 'profile' => 'S'],
                ['text' => 'Sistemático na Organização', 'profile' => 'C'],
            ],
            // Bloco 9
            [
                ['text' => 'Energético e Apressado', 'profile' => 'D'],
                ['text' => 'Articulado na Fala', 'profile' => 'I'],
                ['text' => 'Moderador de Conflitos', 'profile' => 'S'],
                ['text' => 'Preciso nas Informações', 'profile' => 'C'],
            ],
            // Bloco 10
            [
                ['text' => 'Firme em Suas Posições', 'profile' => 'D'],
                ['text' => 'Inspirador para o Grupo', 'profile' => 'I'],
                ['text' => 'Prestativo e Gentil', 'profile' => 'S'],
                ['text' => 'Rigoroso com Prazos', 'profile' => 'C'],
            ],
            // Bloco 11
            [
                ['text' => 'Audacioso e Ousado', 'profile' => 'D'],
                ['text' => 'Charmoso e Envolvente', 'profile' => 'I'],
                ['text' => 'Conciliador e Pacífico', 'profile' => 'S'],
                ['text' => 'Perfeccionista e Cuidadoso', 'profile' => 'C'],
            ],
            // Bloco 12
            [
                ['text' => 'Líder Natural', 'profile' => 'D'],
                ['text' => 'Espontâneo e Divertido', 'profile' => 'I'],
                ['text' => 'Cooperativo e Empático', 'profile' => 'S'],
                ['text' => 'Diplomático e Discreto', 'profile' => 'C'],
            ],
            // Bloco 13
            [
                ['text' => 'Impaciente com Lentidão', 'profile' => 'D'],
                ['text' => 'Faz Amigos Rapidamente', 'profile' => 'I'],
                ['text' => 'Procura Segurança Estável', 'profile' => 'S'],
                ['text' => 'Questiona a Lógica', 'profile' => 'C'],
            ],
            // Bloco 14
            [
                ['text' => 'Orientado para a Ação', 'profile' => 'D'],
                ['text' => 'Gosta de Reconhecimento Público', 'profile' => 'I'],
                ['text' => 'Evita Mudanças Bruscas', 'profile' => 'S'],
                ['text' => 'Planeja Antes de Executar', 'profile' => 'C'],
            ],
            // Bloco 15
            [
                ['text' => 'Determinado a Vencer', 'profile' => 'D'],
                ['text' => 'Convence pela Emoção', 'profile' => 'I'],
                ['text' => 'Sabe Ouvir Atentamente', 'profile' => 'S'],
                ['text' => 'Convence pelos Fatos', 'profile' => 'C'],
            ],
            // Bloco 16
            [
                ['text' => 'Assertivo e Direto', 'profile' => 'D'],
                ['text' => 'Descontraído e Leve', 'profile' => 'I'],
                ['text' => 'Calmo e Tranquilo', 'profile' => 'S'],
                ['text' => 'Reflexivo e Ponderado', 'profile' => 'C'],
            ]
        ];

        foreach ($blocos as $index => $opcoes) {
            $question = DiscQuestion::create([
                'title' => 'Bloco ' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'block_number' => $index + 1,
            ]);

            // Baralhar as opções para não estarem sempre na ordem D, I, S, C
            shuffle($opcoes);

            foreach ($opcoes as $opt) {
                // Converter string para o valor do Enum
                $profileValue = match($opt['profile']) {
                    'D' => DiscProfileEnum::DOMINANCE->value,
                    'I' => DiscProfileEnum::INFLUENCE->value,
                    'S' => DiscProfileEnum::STEADINESS->value,
                    'C' => DiscProfileEnum::COMPLIANCE->value,
                };

                DiscOption::create([
                    'disc_question_id' => $question->id,
                    'text' => $opt['text'],
                    'profile' => $profileValue,
                ]);
            }
        }
    }
}
