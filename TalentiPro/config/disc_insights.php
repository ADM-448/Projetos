<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Intensidade dos Eixos (Adjetivos 10 a 99)
    |--------------------------------------------------------------------------
    | Cada fator possui uma régua de intensidade baseada na pontuação de 0 a 100.
    | O RH pode usar isso para saber exatamente qual a profundidade do traço.
    */
    'intensity_adjectives' => [
        'D' => [
            10 => 'Suave', 20 => 'Cauteloso', 30 => 'Independente', 40 => 'Questionador', 
            50 => 'Constante', 60 => 'Competitivo', 70 => 'Determinado', 80 => 'Ousado', 
            90 => 'Enérgico', 99 => 'Obstinado/Exigente'
        ],
        'I' => [
            10 => 'Retraído', 20 => 'Contemplativo', 30 => 'Reflexivo', 40 => 'Extrovertido', 
            50 => 'Encantador', 60 => 'Otimista', 70 => 'Sociável', 80 => 'Entusiástico', 
            90 => 'Inspirador', 99 => 'Persuasivo'
        ],
        'S' => [
            10 => 'Espontâneo', 20 => 'Agitado', 30 => 'Flexível', 40 => 'Equilibrado', 
            50 => 'Ordenado', 60 => 'Metódico', 70 => 'Estável', 80 => 'Calmo', 
            90 => 'Passivo', 99 => 'Paciente/Previsível'
        ],
        'C' => [
            10 => 'Desafiante', 20 => 'Factual', 30 => 'Conservador', 40 => 'Convincente', 
            50 => 'Responsável', 60 => 'Preparado', 70 => 'Analítico', 80 => 'Cuidadoso', 
            90 => 'Sistemático', 99 => 'Perfeccionista'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sub-traços Discus (Interação entre 2 Eixos Primários)
    |--------------------------------------------------------------------------
    | Quando um eixo primário domina fortemente outro, surge um sub-traço.
    */
    'subtraits' => [
        'D_I' => ['name' => 'Determinação', 'description' => 'Focado em resultados rápidos, pode atropelar processos para atingir metas.'],
        'D_S' => ['name' => 'Automotivação', 'description' => 'Age com senso de urgência e total independência, sem esperar por ordens.'],
        'D_C' => ['name' => 'Independência', 'description' => 'Tende a criar as suas próprias regras em vez de seguir normas estabelecidas rígidas.'],
        
        'I_D' => ['name' => 'Sociabilidade', 'description' => 'Usa a persuasão, o carisma e a influência verbal para liderar e convencer.'],
        'I_S' => ['name' => 'Entusiasmo', 'description' => 'Muito expressivo, enérgico e capaz de inspirar e animar os outros rapidamente.'],
        'I_C' => ['name' => 'Autoconfiança', 'description' => 'Age com segurança pública aparente, mesmo quando não dispõe de todos os factos.'],
        
        'S_D' => ['name' => 'Paciência', 'description' => 'Prefere a harmonia e o ritmo constante à pressão stressante por resultados imediatos.'],
        'S_I' => ['name' => 'Consideração', 'description' => 'Altamente empático, prioriza o bem-estar da equipa e as relações interpessoais.'],
        'S_C' => ['name' => 'Persistência', 'description' => 'Mantém o foco tenaz em tarefas morosas, trabalhando sem perder a consistência.'],
        
        'C_D' => ['name' => 'Cooperação', 'description' => 'Trabalha muito bem sob diretrizes claras e estruturadas, evitando riscos não calculados.'],
        'C_I' => ['name' => 'Exatidão', 'description' => 'Foca estritamente em factos, dados e qualidade, desconfiando do excesso de emoções.'],
        'C_S' => ['name' => 'Sensibilidade', 'description' => 'Altamente atento aos detalhes e às nuances do ambiente, exigindo precisão absoluta.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Guia Prático do Gestor (Por Perfil Primário)
    |--------------------------------------------------------------------------
    */
    'profiles' => [
        'D' => [
            'strengths' => ['Foco extremo em resultados', 'Tomada de decisão rápida sob pressão', 'Iniciativa em resolução de problemas complexos'],
            'environment' => ['Altamente desafiador', 'Livre de micro-gestão', 'Com oportunidades para liderar e competir'],
            'do' => ['Seja direto, frontal e objetivo', 'Apresente os resultados práticos esperados', 'Dê autonomia para executar'],
            'dont' => ['Não perca tempo com detalhes morosos ou irrelevantes', 'Não tente controlá-lo de perto', 'Não leve a sua assertividade como ofensa pessoal'],
            'learning' => 'Aprende melhor enfrentando desafios práticos de imediato e gerindo as coisas "hands-on".'
        ],
        'I' => [
            'strengths' => ['Comunicação persuasiva e carismática', 'Motivação de equipas', 'Pensamento otimista e criativo'],
            'environment' => ['Interativo e social', 'Com reconhecimento público de méritos', 'Flexível e pouco rotineiro'],
            'do' => ['Seja amigável e caloroso na abordagem', 'Permita que ele expresse as suas ideias livremente', 'Reconheça publicamente as suas conquistas'],
            'dont' => ['Não o isole socialmente', 'Não o sobrecarregue com planilhas densas de dados frios', 'Não corte a sua empolgação criativa'],
            'learning' => 'Aprende melhor em dinâmicas de grupo, discutindo conceitos em voz alta e interagindo.'
        ],
        'S' => [
            'strengths' => ['Trabalho em equipa e colaboração', 'Constância, planeamento longo e lealdade', 'Capacidade incrível de saber ouvir'],
            'environment' => ['Estável, calmo e previsível', 'Harmonioso sem conflitos constantes', 'Com rotinas bem definidas'],
            'do' => ['Crie um ambiente psicologicamente seguro', 'Explique o "porquê" de quaisquer mudanças com muita antecedência', 'Mostre apoio genuíno'],
            'dont' => ['Não o force a tomar decisões críticas sob pressão imediata', 'Não promova conflitos agressivos', 'Não altere as regras subitamente'],
            'learning' => 'Aprende melhor absorvendo o conteúdo a um ritmo constante, com manuais claros e mentoria prática.'
        ],
        'C' => [
            'strengths' => ['Qualidade, rigor e precisão técnica', 'Análise crítica profunda', 'Organização estruturada de processos'],
            'environment' => ['Lógico e racional', 'Baseado em dados e regras precisas', 'Livre de pressões emocionais excessivas'],
            'do' => ['Forneça dados, evidências e factos exatos', 'Dê-lhe tempo suficiente para a análise mental', 'Valorize a precisão do seu trabalho'],
            'dont' => ['Não seja vago, superficial ou genérico', 'Não critique o seu trabalho sem fundamentos irrefutáveis', 'Não tome decisões sem planeamento lógico'],
            'learning' => 'Aprende melhor lendo a documentação a fundo, analisando dados estatísticos e desconstruindo o "porquê" sistémico.'
        ]
    ]
];
