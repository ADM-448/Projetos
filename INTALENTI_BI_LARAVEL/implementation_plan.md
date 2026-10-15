# Motor de Busca Salarial Híbrido: Perfil da Empresa & Competências

Para resolver o problema das estimativas de mercado genéricas (que avaliam apenas a "média geral"), vamos transformar a Pesquisa de Mercado em um motor altamente específico que leve em conta tanto a **realidade da empresa** quanto o **perfil técnico do cargo**.

## 1. O Problema Atual
Hoje, se buscarmos "Desenvolvedor Júnior", o sistema nos traz a média padrão. Porém, um Júnior na DB1 (Grande Porte, Desenvolvimento de Software) ganha diferente de um Júnior em uma Startup de Marketing, e também exige habilidades (competências) completamente diferentes.

## 2. A Solução (Próximos Passos)

Para termos salários precisos, a nossa pesquisa (scraping/IA) precisará de 3 Pilares. O pilar 1 já temos (O Cargo). O pilar 2 (Contexto Global) já começamos a esboçar na interface. Falta o pilar 3 e conectar tudo no backend.

### Passo 2.1: Interface de Competências (Frontend)
- **Coluna Nova:** Adicionaremos a coluna "Competências Chave" na tabela da aba "Pesquisa de Mercado".
- **Sistema de Tags:** O usuário (RH) poderá adicionar tags a cada cargo (ex: `AWS`, `Python`, `B2B`, `Fluente em Inglês`).
- **Modal Fluido:** Ao clicar em "Adicionar", um modal interativo (feito com Alpine.js) abrirá para que o RH digite e crie essas tags dinamicamente.

### Passo 2.2: Empacotando o Payload (Frontend -> Backend)
- Quando o usuário clicar em **"Pesquisar Todos"**, o frontend vai capturar o Contexto Global que já temos:
  - `Porte` (ex: Startup)
  - `Segmento` (ex: Tecnologia)
  - `Região` (ex: SP)
- E vai varrer a tabela pegando todas as **Competências** que o RH vinculou a cada cargo, enviando um "Pacote Completo" para o servidor.

### Passo 2.3: O Motor de Pesquisa Híbrida (Backend)
- No `MarketSyncController`, interceptaremos esse "Pacote Completo".
- **Prompt Dinâmico (IA) / Scraping:** O algoritmo de busca deixará de pesquisar apenas "Salário de Desenvolvedor Júnior". Ele passará a pesquisar:
  > *"Estimativa salarial (Piso, Mediana, Teto) para Desenvolvedor Júnior, focado em [AWS, Python], trabalhando em uma [Startup] de [Tecnologia] no estado de [SP]."*
- Isso forçará o retorno de dados cirúrgicos, compatíveis exatamente com a realidade da empresa que está usando a plataforma.

## Open Questions

> [!WARNING]
> Como os dados da base de cargos atualmente são importados de uma planilha inicial do cliente, as Tags de Competências nascerão "vazias" para os cargos importados.
> 
> **Pergunta:** Na hora de você usar, você prefere que:
> 1. O sistema já tente usar a IA para "sugerir" algumas tags óbvias com base no nome do cargo importado?
> 2. Ou prefere que venha tudo 100% vazio e o próprio analista de RH decida preencher na mão só o que for importante?

## Verification Plan
1. **Frontend:** Garantir que o modal de Tags está salvando os chips visuais corretamente por cargo na tabela.
2. **Backend:** Ao clicar em pesquisar, debugar o `Controller` para garantir que o array recebido contenha: `{ cargo: 'Dev', porte: 'startup', regiao: 'SP', tags: ['Python', 'AWS'] }`.
