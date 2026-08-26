# Próximos Passos: Simulador de Dissídio e Finalização

Agora que o motor de leitura de Excel está robusto e a Sessão de Privacidade está funcionando 100%, concluímos o núcleo do painel (Cenários, Enquadramento e Elegibilidade).

De acordo com nossos requisitos iniciais, a **Fase Final** foca em finalizar as abas restantes e refinar o produto para a entrega.

## User Review Required
> [!IMPORTANT]
> Aprovação do plano abaixo para implementarmos a **Aba de Dissídio**.

## Proposed Changes

### 1. Construção da Aba "Dissídio" (Simulador Global)
O Dissídio (reajuste sindical anual) aplica-se a toda a folha. Vamos construir uma interface onde você pode aplicar uma % de reajuste geral (ou criar exceções).
- **Controles**: Input para `% de Reajuste Sindical` (ex: 5%).
- **KPIs do Dissídio**: Exibir o **Impacto Mensal** e o **Impacto Anual** bruto apenas do reajuste.
- **Tabela de Projeção**: Uma tabela listando todos os funcionários, mostrando: Salário Atual -> % de Ajuste -> Novo Salário Projetado.

#### [MODIFY] `resources/js/Pages/Dashboard.vue`
- Adicionar o HTML, formulários e gráficos na condicional `v-if="currentTab === 'dissidio'"`.
- Criar `computed properties` no Vue para calcular o impacto ao vivo (assim que digitar a porcentagem).

### 2. Aba "Pesquisa de Mercado"
- Finalizar a integração com os dados mockados/banco de dados para que os cards de pesquisa de mercado da Catho/Glassdoor sejam alimentados com base no cargo selecionado na barra lateral.

### 3. Melhorias Finais (UI/UX)
- Tratamento de estados vazios (Empty States) se nenhuma planilha foi importada.
- Polimento de tipografia e espaçamento nas tabelas.

## Verification Plan
- Rodar o dev server local.
- Entrar na Aba Dissídio, digitar `4.5` no campo de `%` e verificar se a folha mensal projetada reflete o acréscimo de 4.5% imediatamente.
