# 🚀 Plano de Deploy — IntalenTI BI

## Stack atual
- **Laravel** 12.67 + **Inertia.js** + **Vue 3**
- **PHP** 8.2 | **Node** 22 | **MySQL** (local: `laravelBI`, porta 3307)
- **Queue**: database | **Session**: database | **Cache**: database

---

## Pré-requisitos do Servidor

O servidor precisa ter:
- PHP **8.2+** com extensões: `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `fileinfo`, `openssl`, `tokenizer`, `json`
- MySQL **8.0+**
- Composer **2.x**
- Acesso SSH (recomendado para VPS) ou painel cPanel

> [!IMPORTANT]
> O projeto usa **Queue Worker** (`php artisan queue:work`) para o scraping de dados de mercado. Em hospedagem compartilhada, isso precisa ser configurado como **Cron Job**. Em VPS, use Supervisor.

---

## Etapa 1 — Preparar o Projeto Localmente

### 1.1 Buildar os assets (Vue → JS/CSS estático)
```bash
npm run build
```
Isso gera a pasta `public/build/` com os arquivos compilados.

### 1.2 Limpar caches de desenvolvimento
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 1.3 Exportar o banco de dados local
```bash
# No XAMPP, use mysqldump na porta 3307
"C:\xampp\mysql\bin\mysqldump.exe" -h 127.0.0.1 -P 3307 -u root -proot laravelBI > laravelBI_backup.sql
```

**Para exportar apenas market_salary_data (dados de mercado):**
```bash
"C:\xampp\mysql\bin\mysqldump.exe" -h 127.0.0.1 -P 3307 -u root -proot laravelBI market_salary_data jobs failed_jobs cache sessions > estrutura_e_dados.sql
```

---

## Etapa 2 — Configurar o Banco no Host

### 2.1 Criar banco no painel do host (cPanel / Plesk)
1. Acesse **MySQL Databases** no painel
2. Crie um banco: ex. `usuario_intalentiBi`
3. Crie um usuário MySQL com senha forte
4. **Vincule o usuário ao banco** com permissão ALL PRIVILEGES

### 2.2 Importar a estrutura via migrations
Após o upload dos arquivos (Etapa 3), rode via SSH:
```bash
php artisan migrate --force
```
Ou importe o dump via **phpMyAdmin** se não tiver SSH.

### 2.3 Importar dados de mercado
Via phpMyAdmin → aba "Importar" → selecione `market_data_only.sql`

Ou via SSH:
```bash
mysql -u usuario_banco -p nome_banco < market_data_only.sql
```

---

## Etapa 3 — Upload dos Arquivos

### Arquivos para enviar ao servidor (via FTP / FileZilla):
```
✅ app/
✅ bootstrap/
✅ config/
✅ database/
✅ public/          ← pasta que o domínio aponta
✅ resources/
✅ routes/
✅ storage/         ← manter permissão 775
✅ vendor/          ← ou rodar composer install no servidor
✅ artisan
✅ composer.json
✅ composer.lock

❌ NÃO enviar:
   - node_modules/
   - .env             ← criar novo no servidor
   - .git/
   - resources/js/   ← não precisa, já buildou
```

> [!TIP]
> Se o servidor tem SSH + Composer, é mais rápido enviar tudo sem `vendor/` e rodar `composer install --no-dev --optimize-autoloader` no servidor.

### Apontar o domínio para `public/`
No painel do host, o **Document Root** deve apontar para `public/`, não para a raiz do projeto.

---

## Etapa 4 — Configurar o `.env` no Servidor

Crie o arquivo `.env` no servidor (via SSH ou gerenciador de arquivos do painel):

```dotenv
APP_NAME="IntalenTI BI"
APP_ENV=production
APP_KEY=                     # ← gerar: php artisan key:generate
APP_DEBUG=false
APP_URL=https://seudominio.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306                 # ← porta padrão no host (não 3307)
DB_DATABASE=usuario_intalentiBi
DB_USERNAME=usuario_banco
DB_PASSWORD=SenhaForte123!

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_DISK=local

GEMINI_API_KEY="sua_chave_aqui"
```

---

## Etapa 5 — Comandos Pós-Upload (SSH)

```bash
# 1. Instalar dependências PHP (se não enviou vendor/)
composer install --no-dev --optimize-autoloader

# 2. Gerar APP_KEY
php artisan key:generate

# 3. Rodar migrations (cria todas as tabelas)
php artisan migrate --force

# 4. Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Criar link simbólico para storage (logo da sidebar)
php artisan storage:link

# 6. Permissões das pastas de storage
chmod -R 775 storage bootstrap/cache
```

---

## Etapa 6 — Configurar Queue Worker

### Opção A: cPanel (Hospedagem Compartilhada)
Em **Cron Jobs**, adicione a cada minuto:
```
* * * * * cd /home/usuario/public_html && php artisan queue:work --stop-when-empty --timeout=60 >> /dev/null 2>&1
```

### Opção B: VPS com Supervisor (recomendado)
```ini
[program:intalenti-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/intalenti/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/intalenti/storage/logs/worker.log
```

---

## Etapa 7 — Verificação Final

| Check | Como verificar |
|-------|---------------|
| ✅ Site carrega | Acessar `https://seudominio.com.br` |
| ✅ Dashboard abre | Deve redirecionar para `/dashboard` |
| ✅ Logo aparece | `/storage/img/logo.jpeg` acessível |
| ✅ Upload de planilha funciona | Testar "Nova Base de Dados" |
| ✅ Dados de mercado carregados | Aba "Pesquisa de Mercado" mostra dados |
| ✅ Sessão apaga ao sair | Fechar e reabrir não mantém dados |

> [!WARNING]
> **Nunca** suba o arquivo `.env` com a `GEMINI_API_KEY` para repositórios públicos (GitHub). Adicione `.env` ao `.gitignore`.

---

## ❓ Definir Antes de Começar

1. **Qual tipo de host?** VPS (DigitalOcean, Hostinger VPS) ou hospedagem compartilhada (cPanel)?
2. **Tem SSH disponível?** Isso muda bastante o processo
3. **Já tem domínio configurado?**
4. **Quer exportar os dados de mercado** que já foram raspados ou zerar e reraspar no servidor?
