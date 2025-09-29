# HDC Events - Plataforma de Gerenciamento de Eventos

HDC Events é uma aplicação web desenvolvida com o framework Laravel que permite a criação, gerenciamento e participação em eventos. O projeto foi criado para aplicar conceitos de desenvolvimento full-stack, incluindo autenticação, manipulação de banco de dados e operações CRUD.

## 🚀 Sobre o Projeto

O objetivo principal da plataforma é oferecer um espaço onde organizadores podem divulgar seus eventos e usuários interessados podem encontrar e se inscrever nesses eventos. A aplicação conta com um sistema de autenticação que diferencia os tipos de usuário (participante e organizador), oferecendo dashboards e funcionalidades específicas para cada perfil.

## ✨ Principais Funcionalidades

-   [cite\_start]**Listagem Pública de Eventos:** Uma página inicial onde todos os visitantes podem ver os próximos eventos e buscar por eventos específicos[cite: 19].
-   **Sistema de Autenticação:** Usuários podem se cadastrar e fazer login na plataforma.
-   [cite\_start]**Dashboard de Usuário:** Após o login, o usuário tem acesso a um painel para gerenciar os eventos que criou e visualizar os eventos em que está participando[cite: 18].
-   [cite\_start]**Gerenciamento de Eventos (CRUD):** Organizadores de eventos podem criar, ler, atualizar e deletar seus próprios eventos[cite: 1].
-   [cite\_start]**Upload de Imagem:** Funcionalidade para adicionar uma foto de capa para cada evento durante sua criação ou edição[cite: 1].
-   **Inscrição em Eventos:** Usuários logados podem se inscrever nos eventos de seu interesse.

## 🛠️ Tecnologias Utilizadas

-   [cite\_start]**Backend:** Laravel e PHP [cite: 1]
-   [cite\_start]**Frontend:** HTML5, CSS [cite: 1]
-   [cite\_start]**Banco de Dados:** MySQL (gerenciado com PhpMyAdmin) [cite: 1]
-   **Controle de Versão:** Git

## 📸 Telas da Aplicação

|                         Página Inicial                         |                                Dashboard do Usuário                                |                                 Detalhes do Evento                                  |
| :------------------------------------------------------------: | :--------------------------------------------------------------------------------: | :---------------------------------------------------------------------------------: |
| ![Página Inicial do HDC Events](screenshots/Tela-Inicial.jpeg) | ![Dashboard do usuário com seus eventos](screenshots/Lista-dos-Participantes.jpeg) | ![Página com detalhes de um evento específico](screenshots/Detalhes-do-evento.jpeg) |

## ⚙️ Como Executar o Projeto

Siga os passos abaixo para executar o projeto em seu ambiente local.

```bash
# 1. Clone este repositório
git clone https://github.com/ADM-448/Projetos.git

# 2. Navegue até o diretório do projeto
cd Projetos/hdcevents

# 3. Instale as dependências do Composer
composer install

# 4. Copie o arquivo de ambiente e configure suas variáveis
cp .env.example .env

# 5. Gere a chave da aplicação
php artisan key:generate

# 6. Configure seu banco de dados no arquivo .env
# Exemplo:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hdcevents
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Execute as migrações do banco de dados
php artisan migrate

# 8. Inicie o servidor local
php artisan serve

# 9. Acesse a aplicação em seu navegador
http://127.0.0.1:8000
```

## 👨‍💻 Autor

Desenvolvido por **Ademar Araújo Teisen**.

-   **LinkedIn:** [Ademar Araújo Teisen](https://www.linkedin.com/in/ademar-teisen-38588b334/)
-   **GitHub:** [@ADM-448](https://www.google.com/search?q=https://github.com/ADM-448)
