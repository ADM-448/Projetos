# SDR JARVIS — Design System & Style Reference
> Cockpit editorial de alta precisão para Outbound B2B. Superfícies quentes em Canvas e Soft Meadow, tipografia humanista e ações de alto contraste em Hi-Yellow. Sem estética genérica de IA (sem esferas 3D, neon ou brilhos sci-fi).

**Theme:** light  
**Archetype:** Consultor de alta precisão / Alfaiataria comercial  
**Stack Visual:** Tailwind CSS v4, Inter + Hedvig Letters Serif  

---

## 1. Princípios de Design

1. **Eficiência Operacional & Densidade:** A interface é uma ferramenta de trabalho contínuo (Kanban, cadências, disparos WhatsApp/Gmail). O espaço deve ser aproveitado sem sensação de claustrofobia, priorizando escaneabilidade em menos de 3 segundos.
2. **Identidade Editorial, Não Máquina:** Usamos a elegância sóbria de um assistente executivo humano. O software transmite segurança e estratégia, fugindo totalmente de chatbots espalhafatosos e gradientes roxo/ciano.
3. **Alto Contraste Funcional:** O amarelo (#ffe228) é reservado exclusivamente para o foco primário e conversão direta. A tipografia repousa sobre tinta quase-preta aquecida (Deep Ink #130e30) sobre superfícies com leve toque natural.

---

## 2. Tokens — Cores

| Nome | Valor | Token | Papel na Interface |
|---|---|---|---|
| **Deep Ink** | `#130e30` | `--color-deep-ink` | Texto primário, títulos, ícones ativos, bordas selecionadas e botões secundários escuros |
| **Hi-Yellow** | `#ffe228` | `--color-hi-yellow` | Botão de ação primária (+ Nova Conta, Iniciar Cadência). Máximo 1 a 2 por viewport |
| **Canvas** | `#f9fbf2` | `--color-canvas` | Fundo principal da aplicação (off-white aquecido com suave nuance esverdeada) |
| **Soft Meadow**| `#eff2e5` | `--color-soft-meadow` | Superfície da sidebar, cabeçalho de colunas do Kanban e painéis de navegação |
| **Card White** | `#ffffff` | `--color-card-white` | Fundo exclusivo dos cards de empresas/leads e inputs (para destacar sobre Soft Meadow) |
| **Ink Border** | `rgba(19, 14, 48, 0.08)` | `--color-border-subtle` | Delimitação sutil de cards, colunas e divisores de lista |
| **Slate** | `#5f5c6e` | `--color-slate` | Metadados, telefones, e-mails, placeholders e subtítulos secundários |
| **Status Alert**| `#fee2e2` / `#991b1b` | `--badge-alert-bg` / `text` | Alertas de follow-up atrasado e tarefas críticas |
| **Status Ok** | `#dcfce7` / `#166534` | `--badge-ok-bg` / `text` | Status de conexão de chips WhatsApp e reuniões agendadas |

---

## 3. Tokens — Tipografia

### Hedvig Letters Serif (`--font-hedvig-letters-serif`)
* **Papel:** Títulos de páginas, nomes de contas no Kanban, números principais de métricas (KPIs).
* **Pesos:** 700 (Display/Headlines), 400 (Citações e destaques de contexto).
* **Propósito:** Traz peso consultivo e editorial, distanciando a ferramenta do visual padrão de dashboards genéricos.
* **Escala:**
  * Nome de Conta/Lead no Card: `18px` · line-height `1.2` · tracking `-0.01em`
  * Título de Módulo / Funil: `24px` a `28px` · line-height `1.15`
  * Grandes Números (KPIs): `28px` a `32px` · peso `700`

### Inter (`--font-inter`)
* **Papel:** Todo o texto utilitário: menus, tabelas, dados de decisores, botões, formulários e badges.
* **Pesos:** 400 (dados e corpo), 500 (labels e navegação), 600 (ênfases operacionais).
* **Escala:**
  * Micro (Badges e Contadores): `10px` a `11px` · tracking `-0.02em` · uppercase
  * Dados de Contato / Subtítulos: `12px` a `13px` · line-height `1.4`
  * Ações / Inputs / Menus: `14px` · line-height `1.5`

---

## 4. Geometria & Densidade (Layout Tokens)

* **Grid Base:** 4px e 8px.
* **Border Radius de Produtividade:**
  * Botões de ação, chips de filtros rápidos e tags de status: `1440px` (estilo pílula integral).
  * Cards de empresas e colunas Kanban: `12px` a `16px` (abandonando o raio excessivo de 24px em tabelas e painéis operacionais para evitar perda de área útil).
  * Modais e gavetas laterais: `16px`.
* **Superfície dos Cards (Regra de Camadas):**
  * Nível 0: `Canvas (#f9fbf2)` — Fundo da página.
  * Nível 1: `Soft Meadow (#eff2e5)` — Coluna do funil e Sidebar.
  * Nível 2: `Card White (#ffffff)` com borda `1px solid rgba(19, 14, 48, 0.08)` — Card da conta arrastável.

---

## 5. Diretrizes da Marca & Logotipo

### O que fazer:
* **Monograma "SDR JARVIS":** Tipografia refinada combinando peso forte com o "J" geométrico minimalista e corte técnico.
* **Monocromia:** O logo deve existir primariamente em `Deep Ink (#130e30)` sobre fundos claros.
* **Símbolo:** Caso utilize ícone, deve ser plano (Flat 2D), linear (traço de 1.5px a 2px), representando alinhamento estratégico, cadência ou funil.

### O que NÃO fazer:
* **Sem elementos "Sci-Fi":** Proibido o uso de reatores luminosos, orbes flutuantes, anéis de partículas ou degradês ciano/neon.
* **Sem ícones mágicos genéricos:** Evitar ícones de varinha ou estrelas cintilantes (sparkles ✨) ao lado do nome do produto.
* **Sem sombras pesadas de elevação:** Não usar sombras difusas escuras (`drop-shadow-xl`); o relevo deve ser resolvido via contraste entre superfícies e bordas refinadas.

---

## 6. Componentes Principais do Cockpit

### 1. Card de Conta no Funil (Kanban Card)
* **Container:** Fundo `#ffffff`, borda `1px solid rgba(19, 14, 48, 0.08)`, raio `12px`, padding `14px`.
* **Cabeçalho:** Nome da empresa em Hedvig Letters Serif 700 (`17px`), acompanhado de tag de segmento em pílula cinza suave.
* **Linha de Alerta de Cadência:** Tag compacta com ícone de relógio para follow-ups atrasados (`bg-rose-50 text-rose-800 border border-rose-200`).
* **Bloco de Decisores:** Lista compacta com nome do contato em Inter 500 (`13px`), cargo em Slate (`11px`) e botões rápidos de 1 clique (ícone direto de WhatsApp e E-mail com hover escuro).

### 2. Barra de Navegação Rápida (Smart Views)
* Fundo neutro com botões em cápsula (`1440px`).
* Estado inativo: fundo transparente, texto `#5f5c6e`, borda `1px solid rgba(19, 14, 48, 0.1)`.
* Estado ativo: fundo `#130e30` com texto branco ou `#ffe228` com texto `#130e30` para filtros críticos de atenção imediata.

### 3. Painel de Status Operacional (Sidebar Inferior)
* Exibição de chips de conexão (Evolution API / Gmail) com indicadores de pulso sóbrios:
  * Ponto verde sólido `8px` (`#16a34a`) com texto Inter 500 (`12px`).
  * Sem elementos luminosos ou efeitos de circuito.

---

## 7. Tailwind v4 Theme Configuration

```css
@theme {
  --color-deep-ink: #130e30;
  --color-hi-yellow: #ffe228;
  --color-slate: #5f5c6e;
  --color-canvas: #f9fbf2;
  --color-soft-meadow: #eff2e5;
  --color-card-white: #ffffff;
  --color-border-subtle: rgba(19, 14, 48, 0.08);

  --font-hedvig-letters-serif: 'Hedvig Letters Serif', Georgia, serif;
  --font-inter: 'Inter', system-ui, -apple-system, sans-serif;

  --radius-pill: 1440px;
  --radius-card: 14px;
  --radius-column: 16px;
}