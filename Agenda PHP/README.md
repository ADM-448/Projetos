# 📔 Agenda de Contatos em PHP

Um projeto simples de **CRUD** (Create, Read, Update, Delete) para gerenciar uma agenda de contatos. Este projeto foi desenvolvido como parte dos estudos de PHP, utilizando PHP puro no back-end, PDO para a conexão segura com o banco de dados e Bootstrap para a interface do usuário.

## 🖼️ Tela Principal do Projeto

## ✨ Funcionalidades Principais

- **Listar Contatos:** Visualização de todos os contatos cadastrados em uma tabela na página inicial.
- **Criar Contato:** Formulário para adicionar um novo contato (nome, telefone e observações).
- **Editar Contato:** Formulário para atualizar as informações de um contato existente.
- **Visualizar Contato:** Página individual para ver os detalhes de um único contato.
- **Deletar Contato:** Funcionalidade para remover um contato do banco de dados.
- **Mensagens de Feedback:** O usuário recebe mensagens de sucesso ao criar, editar ou deletar um contato.

---

## 💻 Tecnologias Utilizadas

- **Back-end:**

  - PHP 8.2
  - PDO (PHP Data Objects) para conexão com o banco de dados.

- **Front-end:**

  - HTML5
  - CSS3
  - Bootstrap 4

- **Banco de Dados:**

  - MySQL

- **Ambiente de Desenvolvimento:**

  - Servidor Apache (XAMPP)

---

## 🚀 Como Executar o Projeto Localmente

Siga os passos abaixo para rodar o projeto no seu computador.

**1. Pré-requisitos:**

- Ter um ambiente de desenvolvimento PHP, como o [XAMPP](https://www.apachefriends.org/pt_br/index.html), instalado e rodando.

**2. Clone o Repositório:**

```bash
git clone https://github.com/seu-usuario/seu-repositorio.git
```

_(Lembre-se de trocar pela URL do seu repositório no GitHub)_

**3. Mova a Pasta:**

- Mova a pasta do projeto para o diretório `htdocs` do seu XAMPP (geralmente em `C:\xampp\htdocs`).

**4. Crie o Banco de Dados:**

- Inicie os módulos **Apache** e **MySQL** no seu painel do XAMPP.
- Acesse o phpMyAdmin pelo seu navegador (ex: `http://localhost/phpmyadmin` ou `http://localhost:3307/phpmyadmin`).
- Crie um novo banco de dados com o nome `agenda`.
- Selecione o banco `agenda` e vá para a aba **SQL**. Copie e cole o código abaixo e execute:

<!-- end list -->

```sql
CREATE TABLE contacts (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    observations TEXT
);
```

**5. Configure a Conexão:**

- Abra o arquivo `config/connection.php`.
- Verifique se as variáveis `$host`, `$port`, `$user`, `$pass` e `$db` correspondem à configuração do seu banco de dados local.

<!-- end list -->

```php
$host = 'localhost'; // ou '127.0.0.1'
$port = 3307; // A porta que seu MySQL usa no XAMPP
$user = 'root';
$pass = ''; // Sua senha do MySQL, se houver
$db = 'agenda';
```

**6. Acesse o Projeto:**

- Abra seu navegador e acesse a URL correspondente (ex: `http://localhost/nome-da-pasta-do-projeto/`).

Pronto\! A agenda de contatos deve estar funcionando.
