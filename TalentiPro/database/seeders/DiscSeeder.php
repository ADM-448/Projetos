<?php

namespace Database\Seeders;

use App\Models\DiscOption;
use App\Models\DiscQuestion;
use Illuminate\Database\Seeder;

class DiscSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa os dados antigos para evitar duplicação
        DiscOption::query()->delete();
        DiscQuestion::query()->delete();

        // Os 25 blocos extraídos diretamente da planilha do sistema
        $blocos = [
            1 => [
                'title' => 'Tende a agir de forma...',
                'DOMINANCE' => 'Assertiva',
                'INFLUENCE' => 'Persuasiva',
                'STEADINESS' => 'Paciente',
                'COMPLIANCE' => 'Contemplativa',
            ],
            2 => [
                'title' => 'Confortável com...',
                'DOMINANCE' => 'Ser decisivo',
                'INFLUENCE' => 'Amizade social',
                'STEADINESS' => 'Ser parte de um time',
                'COMPLIANCE' => 'Planejamento e ordem',
            ],
            3 => [
                'title' => 'Desejo de...',
                'DOMINANCE' => 'Variedade',
                'INFLUENCE' => 'Menos estrutura',
                'STEADINESS' => 'Harmonia',
                'COMPLIANCE' => 'Lógica',
            ],
            4 => [
                'title' => 'Sob estresse pode se tornar...',
                'DOMINANCE' => 'Ditatorial',
                'INFLUENCE' => 'Sarcástico',
                'STEADINESS' => 'Submisso',
                'COMPLIANCE' => 'Arredio',
            ],
            5 => [
                'title' => 'Característica principal...',
                'DOMINANCE' => 'Franco',
                'INFLUENCE' => 'Otimista',
                'STEADINESS' => 'Serviçal',
                'COMPLIANCE' => 'Ordeiro',
            ],
            6 => [
                'title' => 'Quando em conflito, esse estilo…',
                'DOMINANCE' => 'Demanda ação',
                'INFLUENCE' => 'Ataca',
                'STEADINESS' => 'Reclama',
                'COMPLIANCE' => 'Evita',
            ],
            7 => [
                'title' => 'Força aparente...',
                'DOMINANCE' => 'Solucionador de problemas',
                'INFLUENCE' => 'Encorajador',
                'STEADINESS' => 'Supporter',
                'COMPLIANCE' => 'Organizador',
            ],
            8 => [
                'title' => 'Com erros...',
                'DOMINANCE' => 'Informa o erro diretamente',
                'INFLUENCE' => 'Chama a pessoa e explica o erro',
                'STEADINESS' => 'Fica calado e aceita o erro',
                'COMPLIANCE' => 'Se incomoda e questiona',
            ],
            9 => [
                'title' => 'Sob estresse pode se tornar...',
                'DOMINANCE' => 'Crítico',
                'INFLUENCE' => 'Superficial',
                'STEADINESS' => 'Indeciso',
                'COMPLIANCE' => 'Cabeça dura',
            ],
            10 => [
                'title' => 'Pode ser considerado...',
                'DOMINANCE' => 'Impaciente',
                'INFLUENCE' => 'Inoportuno',
                'STEADINESS' => 'Indeciso',
                'COMPLIANCE' => 'Inseguro',
            ],
            11 => [
                'title' => 'Necessita de....',
                'DOMINANCE' => 'Controle',
                'INFLUENCE' => 'Aprovação',
                'STEADINESS' => 'Rotina',
                'COMPLIANCE' => 'Padrão',
            ],
            12 => [
                'title' => 'Limitação desse perfil...',
                'DOMINANCE' => 'Direto',
                'INFLUENCE' => 'Desorganizado',
                'STEADINESS' => 'Indireto',
                'COMPLIANCE' => 'Detalhista',
            ],
            13 => [
                'title' => 'Possui medo de...',
                'DOMINANCE' => 'Perder',
                'INFLUENCE' => 'Rejeição',
                'STEADINESS' => 'Mudanças bruscas',
                'COMPLIANCE' => 'Estar errado',
            ],
            14 => [
                'title' => 'Mensura desempenho com...',
                'DOMINANCE' => 'Resultados',
                'INFLUENCE' => 'Reconhecimento',
                'STEADINESS' => 'Compatibilidade',
                'COMPLIANCE' => 'Precisão',
            ],
            15 => [
                'title' => 'Com subalternos, costuma ser...',
                'DOMINANCE' => 'Orgulhoso',
                'INFLUENCE' => 'Permissivo',
                'STEADINESS' => 'Humilde',
                'COMPLIANCE' => 'Cauteloso',
            ],
            16 => [
                'title' => 'Abordagem primária...',
                'DOMINANCE' => 'Independente',
                'INFLUENCE' => 'Interativo',
                'STEADINESS' => 'Estável',
                'COMPLIANCE' => 'Corretivo',
            ],
            17 => [
                'title' => 'Outra limitação desse perfil...',
                'DOMINANCE' => 'Intenso',
                'INFLUENCE' => 'Não tradicional',
                'STEADINESS' => 'Indeciso',
                'COMPLIANCE' => 'Impessoal',
            ],
            18 => [
                'title' => 'Ponto cego...',
                'DOMINANCE' => 'Ser responsabilizado',
                'INFLUENCE' => 'Realizar compromissos',
                'STEADINESS' => 'Necessidade de mudança',
                'COMPLIANCE' => 'Tomada de decisão',
            ],
            19 => [
                'title' => 'Mensura desempenho com...',
                'DOMINANCE' => 'Histórico',
                'INFLUENCE' => 'Elogios',
                'STEADINESS' => 'Contribuição',
                'COMPLIANCE' => 'Qualidade dos resultados',
            ],
            20 => [
                'title' => 'Prefere tarefas....',
                'DOMINANCE' => 'Desafiadoras',
                'INFLUENCE' => 'Relacionada a pessoas',
                'STEADINESS' => 'Agendadas',
                'COMPLIANCE' => 'Estruturadas',
            ],
            21 => [
                'title' => 'Com atrasos...',
                'DOMINANCE' => 'Se irrita e confronta',
                'INFLUENCE' => 'Nem liga, está distraído',
                'STEADINESS' => 'Sabe do atraso, mas aceita',
                'COMPLIANCE' => 'Reclama e analisa a situação',
            ],
            22 => [
                'title' => 'Em situações extremas...',
                'DOMINANCE' => 'Se preocupa demais com metas',
                'INFLUENCE' => 'Fala sem pensar',
                'STEADINESS' => 'Procrastina ao invés de fazer',
                'COMPLIANCE' => 'Analisa demais',
            ],
            23 => [
                'title' => 'Precisa melhorar...',
                'DOMINANCE' => 'Empatia e Paciência',
                'INFLUENCE' => 'Contole emocional',
                'STEADINESS' => 'Ser assertivo sob pressão',
                'COMPLIANCE' => 'Se preocupar menos sobre tudo',
            ],
            24 => [
                'title' => 'Em uma discussão...',
                'DOMINANCE' => 'Busca ter a razão',
                'INFLUENCE' => 'Busca diminuir o conflito',
                'STEADINESS' => 'Busca concordância',
                'COMPLIANCE' => 'Busca comprovar sua opinião',
            ],
            25 => [
                'title' => 'Quando vai às compras...',
                'DOMINANCE' => 'Sabe o que quer',
                'INFLUENCE' => 'Se diverte',
                'STEADINESS' => 'Fica indeciso',
                'COMPLIANCE' => 'Busca ofertas',
            ],
        ];

        foreach ($blocos as $numeroBloco => $dados) {
            $question = DiscQuestion::create([
                'block_number' => $numeroBloco,
                'title' => $dados['title'],
            ]);

            // Grava as 4 opções mantendo a relação de perfil (D, I, S, C)
            foreach ($dados as $perfil => $frase) {
                if ($perfil === 'title') {
                    continue;
                }

                DiscOption::create([
                    'disc_question_id' => $question->id,
                    'text' => $frase,
                    'profile' => $perfil,
                ]);
            }
        }
    }
}
