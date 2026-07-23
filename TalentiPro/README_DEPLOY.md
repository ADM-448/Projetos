# 🚀 GUIA DE DEPLOY - TALENTIPRO (HOSTGATOR)

Este guia contém todas as alterações necessárias para que o projeto funcione na subpasta `/TalentiPro` da HostGator.

---

## 1. Arquivo `.env` (No Servidor)
Crie ou edite o arquivo `.env` na raiz do projeto (`/public_html/TalentiPro/`) com estas configurações básicas:

```env
APP_NAME=TalentiPro
APP_ENV=production
APP_DEBUG=false
APP_URL=https://intalenti.com.br/TalentiPro
ASSET_URL=https://intalenti.com.br/TalentiPro

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=intale75_talentipro
DB_USERNAME=intale75
DB_PASSWORD=SUA_SENHA_AQUI
```

---

## 2. Configurações de Código (Arquivos PHP)

### A. Forçar HTTPS e Subpasta
Edite o arquivo `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if (app()->environment('production')) {
        \URL::forceRootUrl(config('app.url'));
        \URL::forceScheme('https');
    }
}
```

---

## 3. Arquivos de Roteamento (.htaccess)

### A. RAÍZ DO PROJETO (`/public_html/TalentiPro/.htaccess`)
Este arquivo garante que o site abra sem precisar digitar `/public` na URL.

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redireciona assets para a pasta public/build
    RewriteRule ^build/(.*)$ public/build/$1 [L]
    RewriteRule ^assets/(.*)$ public/assets/$1 [L]
    RewriteRule ^storage/(.*)$ public/storage/$1 [L]

    # Manda o resto para o index.php dentro de public
    RewriteRule ^$ public/index.php [L]
    RewriteRule ^((?!public/).*)$ public/index.php/$1 [L]
</IfModule>
```

---

## 4. Passo a Passo do Upload

1. **Limpeza:** Apague tudo que está em `/public_html/TalentiPro/` no servidor.
2. **Build Local:** No seu PC, rode `npm run build` para gerar o CSS/JS final.
3. **Upload via FileZilla:**
   - Suba as pastas: `app`, `bootstrap`, `config`, `database`, `lang`, `public`, `resources`, `routes`, `vendor`.
   - Suba os arquivos: `.env`, `.htaccess`, `artisan`, `package.json`.
4. **Permissões (Terminal cPanel):**
   ```bash
   cd public_html/TalentiPro
   chmod -R 775 storage bootstrap/cache
   php artisan optimize:clear
   php artisan optimize
   ```

---

## 5. Dicas de Ouro
- **Erro 419:** Aperte Ctrl + F5 no navegador.
- **Erro 500:** Verifique se o banco de dados e usuário estão vinculados no cPanel.
- **CSS não carrega:** Verifique se a pasta `public/build` foi enviada corretamente.

---

## 6. Previsão de Custos (Infraestrutura Escalável - Prefeituras)

*Anotações para a fase de escala em produção (Cloudways + DO)*

- **Servidor da Aplicação (Cloudways / DigitalOcean)**
  - Plano: General Purpose (8GB RAM / 2 Cores)
  - Custo Estimado: ~$98.00 USD/mês
- **Armazenamento de Arquivos/Banco Separado (DigitalOcean Block Storage / Spaces)**
  - Capacidade: 1000 GiB (1 TB)
  - Custo Estimado: ~$10.00 a $100.00 USD/mês (Volume vs Object Storage). *Nota da imagem: 100GiB é $10, 1000GiB é $100*.
- **IA (DeepSeek)**
  - Preço: ~$0.87 USD a cada 1 Milhão de Tokens (Output). Extremamente viável para processar reuniões.
