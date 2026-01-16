# Documentação do Projeto IntalenTi Vagas MVC

start-> Start-Process http://localhost:8000"; php -S localhost:8000 server.php

## Visão Geral

Sistema de gerenciamento de vagas de emprego desenvolvido em PHP com arquitetura MVC, suportando múltiplos portais (multi-tenancy) para diferentes cidades/prefeituras.

---

## Tecnologias Utilizadas

| Tecnologia | Versão/Descrição |
|------------|------------------|
| PHP        | 7.4+             |
| MySQL/MariaDB | 10.x+         |
| Bootstrap  | 5.x              |
| Chart.js   | Para gráficos    |
| Leaflet.js | Para mapas       |
| Composer   | Autoload PSR-4   |

---

## Estrutura de Diretórios

```
IntalenTIVagasMVC/
├── public/                 # Pasta pública (document root)
│   ├── index.php           # Ponto de entrada da aplicação
│   ├── assets/             # CSS, JS, imagens públicas
│   └── uploads/            # Uploads de usuários (logos, currículos)
│
├── src/                    # Código-fonte da aplicação
│   ├── Config/
│   │   └── Database.php    # Conexão com banco (Singleton)
│   │
│   ├── Controllers/        # Controladores MVC
│   │   ├── AuthController.php
│   │   ├── VagasController.php
│   │   ├── DashboardController.php
│   │   ├── AdminController.php
│   │   ├── PrefeituraController.php
│   │   └── ...
│   │
│   ├── Models/             # Modelos de dados
│   │   ├── User.php
│   │   ├── Vaga.php
│   │   ├── Empresa.php
│   │   ├── Candidatura.php
│   │   └── Relatorio.php
│   │
│   ├── Views/              # Templates de visualização
│   │   ├── layouts/        # Master layouts
│   │   ├── components/     # Header, footer, etc.
│   │   ├── Auth/           # Login, registro
│   │   ├── Vagas/          # Listagem, detalhe, criação
│   │   ├── Dashboard/      # Painel RH
│   │   ├── Prefeitura/     # Observatório
│   │   └── Admin/          # Gestão de portais
│   │
│   ├── Core/               # Classes base do framework
│   │   ├── Core.php        # Roteador principal
│   │   └── Controller.php  # Controller base
│   │
│   └── Utils/              # Utilitários
│       └── GeoUtils.php    # Geolocalização via CEP
│
├── vendor/                 # Dependências Composer
├── composer.json           # Configuração Composer
└── BACKLOG.md              # Funcionalidades futuras
```

---

## Banco de Dados

### Tabelas Principais

#### `users`
| Coluna        | Tipo         | Descrição                     |
|---------------|--------------|-------------------------------|
| id            | INT (PK)     | ID do usuário                 |
| name          | VARCHAR(255) | Nome completo                 |
| email         | VARCHAR(255) | E-mail (único)                |
| password      | VARCHAR(255) | Senha (hash bcrypt)           |
| cpf           | VARCHAR(14)  | CPF (somente candidatos)      |
| tipo_usuario  | TINYINT      | 1=Candidato, 2=RH, 3=Admin    |
| portal_id     | INT (FK)     | ID do portal (multi-tenancy)  |
| created_at    | DATETIME     | Data de criação               |

#### `empresas`
| Coluna        | Tipo         | Descrição                     |
|---------------|--------------|-------------------------------|
| id            | INT (PK)     | ID da empresa                 |
| nome_empresa  | VARCHAR(255) | Razão social                  |
| nome_fantasia | VARCHAR(255) | Nome fantasia (exibição)      |
| cnpj          | VARCHAR(18)  | CNPJ (único)                  |
| tipo          | ENUM         | empresa, prefeitura, rh       |
| dominio       | VARCHAR(255) | Domínio do portal             |
| portal_id     | INT (FK)     | Vinculado a qual portal       |
| status        | TINYINT      | 0=Inativo, 1=Ativo            |
| logo_path     | VARCHAR(255) | Caminho do logo               |

#### `vagas`
| Coluna            | Tipo         | Descrição                  |
|-------------------|--------------|----------------------------|
| id                | INT (PK)     | ID da vaga                 |
| empresa_id        | INT (FK)     | Empresa que criou          |
| recrutador_id     | INT (FK)     | Usuário RH responsável     |
| portal_id         | INT (FK)     | Portal de origem           |
| titulo            | VARCHAR(255) | Título da vaga             |
| descricao         | TEXT         | Descrição completa         |
| requisitos        | TEXT         | Requisitos                 |
| beneficios        | TEXT         | Benefícios                 |
| localizacao       | VARCHAR(255) | Cidade/Estado              |
| latitude/longitude| DECIMAL      | Coordenadas (geoloc)       |
| faixa_salarial_*  | DECIMAL      | Min/Max salário            |
| confidencial      | TINYINT      | Oculta nome da empresa     |

#### `candidaturas`
| Coluna        | Tipo         | Descrição                     |
|---------------|--------------|-------------------------------|
| id            | INT (PK)     | ID da candidatura             |
| vaga_id       | INT (FK)     | Vaga aplicada                 |
| candidato_id  | INT (FK)     | ID do candidato               |
| status        | VARCHAR(50)  | pendente, analisando, etc.    |
| created_at    | DATETIME     | Data da aplicação             |

#### `candidatos`
| Coluna        | Tipo         | Descrição                     |
|---------------|--------------|-------------------------------|
| id            | INT (PK)     | ID do candidato               |
| user_id       | INT (FK)     | Usuário vinculado             |
| nome_completo | VARCHAR(255) | Nome para currículo           |
| latitude/longitude | DECIMAL | Localização do candidato      |
| cidade/estado | VARCHAR      | Endereço                      |

---

## Sistema Multi-tenancy

### Conceito
Cada cidade/prefeitura opera como um "portal" independente, com dados isolados.

### Fluxo de Detecção
1. Usuário acessa `vagas.cidade.gov.br`.
2. `index.php` captura o `HTTP_HOST`.
3. Busca no banco: `SELECT id FROM empresas WHERE tipo='prefeitura' AND dominio=?`
4. Define `PORTAL_ATUAL` com o ID encontrado (ou fallback).
5. Todas as queries filtram por `portal_id = PORTAL_ATUAL`.

### Arquivos Envolvidos
- `public/index.php` - Detecção de domínio
- `src/Controllers/AuthController.php` - Validação no login/registro
- `src/Models/Vaga.php` - Filtros em listagens
- `src/Models/Empresa.php` - Filtros em listagens

---

## Tipos de Usuário

| Tipo | Código | Permissões |
|------|--------|------------|
| Candidato | 1 | Ver vagas, aplicar, chat |
| RH/Empresa | 2 | Criar vagas, ver candidatos |
| Admin | 3 | Gestão de portais, relatórios |

---

## Rotas Principais

### Públicas
| Rota | Descrição |
|------|-----------|
| `/` | Página inicial |
| `/vagas` | Lista de vagas |
| `/vagas/show/{id}` | Detalhe da vaga |
| `/auth/login` | Tela de login |
| `/auth/register` | Cadastro |

### Candidato (tipo 1)
| Rota | Descrição |
|------|-----------|
| `/curriculo` | Editar currículo |
| `/candidatura/aplicar` | Aplicar para vaga |
| `/proximas` | Vagas próximas (geoloc) |

### RH (tipo 2)
| Rota | Descrição |
|------|-----------|
| `/dashboard` | Minhas vagas |
| `/vagas/create` | Criar vaga |
| `/vagas/edit/{id}` | Editar vaga |
| `/vagas/candidatos/{id}` | Ver candidatos |
| `/empresa` | Configurar empresa |

### Admin (tipo 3)
| Rota | Descrição |
|------|-----------|
| `/admin/empresas` | Listar empresas/portais |
| `/admin/nova` | Criar empresa/portal |
| `/admin/editar/{id}` | Editar |
| `/prefeitura/dashboard` | Observatório |

---

## Funcionalidades Implementadas

### Core
- [x] Arquitetura MVC com roteamento dinâmico
- [x] Autoload PSR-4 via Composer
- [x] Singleton para conexão com banco
- [x] Sistema de sessões e autenticação
- [x] Flash messages

### Multi-tenancy
- [x] Detecção de portal por domínio
- [x] Isolamento de dados (vagas, users, empresas)
- [x] Bloqueio de login cross-portal
- [x] Admin global com acesso a todos os portais

### Candidatos
- [x] Cadastro com validação CPF
- [x] Currículo editável
- [x] Aplicação para vagas
- [x] Questionários de vaga
- [x] Geolocalização (vagas próximas)

### RH/Empresas
- [x] Cadastro com validação CNPJ
- [x] Criação/edição de vagas
- [x] Visualização de candidatos
- [x] Sistema de convites para equipe
- [x] Logo da empresa

### Observatório (Prefeitura)
- [x] KPIs: Vagas, Empresas, Candidatos
- [x] Gráfico de vagas por setor
- [x] Mapa de calor de vagas
- [x] Relatórios comparativos

---

## Configuração do Ambiente

### Requisitos
- PHP 7.4+
- MySQL/MariaDB
- Composer
- Apache com mod_rewrite

### Instalação

1. Clone o repositório.
2. Execute `composer install`.
3. Configure `src/Config/Database.php` com suas credenciais.
4. Importe as migrations ou crie as tabelas manualmente.
5. Aponte o document root do Apache para a pasta `public/`.

### .htaccess (public/)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]
```

---

## Segurança

- Senhas hasheadas com `password_hash()` (bcrypt).
- Prepared statements em todas as queries.
- Validação de CPF/CNPJ no backend.
- Verificação de permissões em cada controller.
- Sanitização de inputs com `filter_input()`.

---

## Próximos Passos (Backlog)

- [ x ] Sistema de notificações em tempo real
- [ x ] Chat entre candidato e recrutador
- [ x ] Painel de métricas avançadas
- [  ] API REST para integrações
- [  ] App mobile (PWA)

---

## Créditos

Desenvolvido com assistência de IA (Antigravity/Claude). E o Dev Master Ademar que criou o sistema baseado no antigo sistema de vagas do IntalenTi.


# Correções e ajustes
tirar idioma no cadastro de candidatos

incluir data nascimnto em cadastro de candidatos, telefone e celular

ola, fulano (contar caracteres ate o espaço)

colocar um espaco entre as cancelar candidatura e voltar para a lista de vagas
quando o cara cadastrar em uma vaga, na tela de vagas aparece uma info na div que ele ja cadastrou e data (candidatou-se data X) (igual nas minhas candidaturas)
![alt text](image.png) Alinhar cancelar com o botao de status (minhas candidaturas)

quando o candidato cadastrar no sistema aparecer na notificação informando para preencher o curriculo
Não posso candidatar sem montar o curriculo mostrar um card na hr que candidatar

idioma no curriculo:
ingles, espanhol, portuhues, e outros (abre card para texto (com basioco, intermediario, avançado))

colocar validador de cep no curriculo 

definir numero de carc em resumo profisional

mover anexar pdf curriculo no final do cadastro


arrumar data de só selcionar no curriculo (validador antes do tempo) não pode colocar data inicio no futuro ( e deixar layout mais amigavel)

trocar 'grau' por 'escolaridade' no cadastro de candidatos

popup mais bonito excluir item em curriculo

## o curriculo vai ser dados minimos até o sobre mim (exceto resumo profissional) para cima (validador antes de canidatar a vaga)

trocar voltar dashboard para voltar

no cadastro trocar no inicio de botao para (eu sou candidato) e (eu sou empresa) e (eu sou prefeitura) em vez do botão tipo organizaçao dentro cadastro

colocar cnpj como primeiro campo cadastro

trocar razao social por nome empresarial (prefeitura)-> colocar card opção municio de maringa e tirar nome fantasia

colocar painel para Empresa ()

no cadastro avisar campos incompletos

tirar section tipo vaga do cadastro de vaga e incluir no contrato jovem aprendiz, 1° emprego ( emfin os outrso q não tem no tipo contrato)


ramo de atividade alterar outro para outros

salario validade de min e max e formatar automaticamente com ponto e virgula


texto de de validação: “Atenção: você respondeu ‘Não’ a um requisito obrigatório para esta vaga. Por esse motivo, não será possível prosseguir com a sua candidatura.”


arrumar cep no curriculo (muito onge dá 'N/D')

data de cadastro do candidato no dash prefeitura consta errado

no mapa de calor colocar mancha  de vaga e de candidato

um dashboard para empresa igual da prefeitura só q só dele