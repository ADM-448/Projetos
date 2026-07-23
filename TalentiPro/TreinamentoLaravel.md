# 📚 Guia Completo: CRUD Laravel do Zero

> **Guia mastigadinho** para criar um sistema completo com Laravel: CRUD, Autenticação, Middleware e muito mais!

---

## 📋 Índice

1. [Configuração Inicial](#1-configuração-inicial)
2. [Estrutura HTML/CSS](#2-estrutura-htmlcss)
3. [CRUD Completo](#3-crud-completo)
4. [Sistema de Autenticação](#4-sistema-de-autenticação)
5. [Middleware e Níveis de Acesso](#5-middleware-e-níveis-de-acesso)
6. [Macetes e Dicas](#6-macetes-e-dicas)

---

## 1. Configuração Inicial

### 1.1 Criar Projeto Laravel

```bash
# Criar novo projeto
composer create-project laravel/laravel meu-projeto

# Entrar na pasta
cd meu-projeto

# Configurar banco de dados no .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=root
DB_PASSWORD=
```

### 1.2 Rodar o Projeto

```bash
# Iniciar servidor
php artisan serve

# Em outro terminal, rodar Vite (para CSS/JS)
npm install
npm run dev
# 1. Garante que o pacote está no projeto
composer require laravel/breeze --dev

# 2. Esse comando CRIA o arquivo auth.php e as views de login
php artisan breeze:install blade
```

**Acesse:** http://localhost:8000

---

## 2. Estrutura HTML/CSS

### 2.1 Criar Layout Base

**Arquivo:** `resources/views/layout/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu App - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- HEADER -->
    <header class="sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center px-6 py-4">
            <h1 class="text-2xl font-bold">Meu Sistema</h1>
            <nav>
                <ul class="flex gap-2">
                    <li><a href="/">Home</a></li>
                    <li><a href="/tarefas">Tarefas</a></li>
                    <li><a href="/tarefas/create">Nova Tarefa</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- CONTEÚDO -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container mx-auto px-6">
            <p>© {{ date('Y') }} - Meu Sistema</p>
        </div>
    </footer>
</body>
</html>
```

### 2.2 CSS Minimalista

**Arquivo:** `resources/css/app.css`

```css
@import 'tailwindcss';

/* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #f7fafc;
    min-height: 100vh;
    color: #1a202c;
}

/* Header */
header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

header nav ul li a {
    color: #4a5568;
    text-decoration: none;
    transition: color 0.2s;
}

header nav ul li a:hover {
    color: #3182ce;
}

/* Botões */
.btn {
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: #3182ce;
    color: white;
}

.btn-primary:hover {
    background: #2c5282;
}

.btn-secondary {
    background: white;
    color: #4a5568;
    border: 1px solid #cbd5e0;
}

.btn-secondary:hover {
    background: #f7fafc;
}

/* Inputs */
input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
textarea {
    border: 1px solid #cbd5e0;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    width: 100%;
    transition: border-color 0.2s;
}

input:focus,
textarea:focus {
    outline: none;
    border-color: #3182ce;
}

/* Footer */
footer {
    background: white;
    border-top: 1px solid #e2e8f0;
    padding: 1.5rem 0;
    text-align: center;
    color: #718096;
    margin-top: 3rem;
}
```

### 2.3 Estrutura de Pastas Views

```
resources/views/
├── layout/
│   └── app.blade.php          # Layout base
├── tarefas/
│   ├── index.blade.php        # Listar
│   ├── create.blade.php       # Criar
│   └── edit.blade.php         # Editar
└── auth/
    ├── login.blade.php        # Login
    └── register.blade.php     # Registro
```

---

## 3. CRUD Completo


# Cria o Model e o arquivo de Migration de uma vez só:
php artisan make:model Category -m

### 3.1 Criar Migration (Banco de Dados)

```bash
php artisan make:migration create_tarefas_table
```

**Arquivo:** `database/migrations/xxxx_create_tarefas_table.php`

```php
public function up()
{
    Schema::create('tarefas', function (Blueprint $table) {
        $table->id();
        $table->string('titulo');
        $table->text('descricao')->nullable();
        $table->boolean('concluida')->default(false);
        $table->date('data_inicio')->nullable();
        $table->date('data_limite')->nullable();
        $table->timestamps();
    });
}
```

**Rodar migration:**

```bash
php artisan migrate
```

### 3.2 Criar Model

```bash
php artisan make:model Tarefa
```

**Arquivo:** `app/Models/Tarefa.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'titulo',
        'descricao',
        'concluida',
        'data_inicio',
        'data_limite'
    ];

    // Conversão automática de tipos
    protected $casts = [
        'concluida' => 'boolean',
        'data_inicio' => 'date',
        'data_limite' => 'date',
    ];
}
```

### 3.3 Criar Controller

```bash
php artisan make:controller TarefaController
```

**Arquivo:** `app/Http/Controllers/TarefaController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    // LISTAR todas as tarefas
    public function index()
    {
        $tarefas = Tarefa::all();
        return view('tarefas.index', compact('tarefas'));
    }

    // MOSTRAR formulário de criação
    public function create()
    {
        return view('tarefas.create');
    }

    // SALVAR nova tarefa (INSERT)
    public function store(Request $request)
    {
        // Validação
        $request->validate([
            'titulo' => 'required|max:255',
            'descricao' => 'nullable',
            'data_inicio' => 'nullable|date',
            'data_limite' => 'nullable|date',
        ]);

        // Criar tarefa
        Tarefa::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'concluida' => $request->has('concluida') ? 1 : 0,
            'data_inicio' => $request->data_inicio,
            'data_limite' => $request->data_limite,
        ]);

        return redirect()->route('tarefas.index')
            ->with('success', 'Tarefa criada com sucesso!');
    }

    // MOSTRAR formulário de edição
    public function edit($id)
    {
        $tarefa = Tarefa::findOrFail($id);
        return view('tarefas.edit', compact('tarefa'));
    }

    // ATUALIZAR tarefa (UPDATE)
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|max:255',
        ]);

        $tarefa = Tarefa::findOrFail($id);
        $tarefa->update([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'concluida' => $request->has('concluida') ? 1 : 0,
            'data_inicio' => $request->data_inicio,
            'data_limite' => $request->data_limite,
        ]);

        return redirect()->route('tarefas.index')
            ->with('success', 'Tarefa atualizada!');
    }

    // EXCLUIR tarefa (DELETE)
    public function destroy($id)
    {
        $tarefa = Tarefa::findOrFail($id);
        $tarefa->delete();

        return redirect()->route('tarefas.index')
            ->with('success', 'Tarefa excluída!');
    }
}
```

### 3.4 Criar Rotas

**Arquivo:** `routes/web.php`

```php
<?php

use App\Http\Controllers\TarefaController;
use Illuminate\Support\Facades\Route;

// Rota inicial
Route::get('/', function () {
    return view('welcome');
});

// Rotas do CRUD de Tarefas
Route::get('/tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
Route::get('/tarefas/create', [TarefaController::class, 'create'])->name('tarefas.create');
Route::post('/tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
Route::get('/tarefas/{id}/edit', [TarefaController::class, 'edit'])->name('tarefas.edit');
Route::put('/tarefas/{id}', [TarefaController::class, 'update'])->name('tarefas.update');
Route::delete('/tarefas/{id}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');
```

**💡 Atalho:** Use Resource Route (faz tudo de uma vez!)

```php
Route::resource('tarefas', TarefaController::class);
```

### 3.5 Views do CRUD

#### **index.blade.php** (Listar)

```blade
@extends('layout.app')

@section('title', 'Tarefas')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Minhas Tarefas</h1>
        
        <a href="{{ route('tarefas.create') }}" class="btn btn-primary mb-4">Nova Tarefa</a>

        <table class="w-full">
            <thead>
                <tr class="border-b-2">
                    <th class="px-4 py-3 text-left">Título</th>
                    <th class="px-4 py-3 text-left">Descrição</th>
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tarefas as $tarefa)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-4">
                            {{ $tarefa->concluida ? '✅' : '⏳' }}
                            {{ $tarefa->titulo }}
                        </td>
                        <td class="px-4 py-4">{{ $tarefa->descricao ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('tarefas.edit', $tarefa->id) }}" class="btn btn-primary">Editar</a>
                                
                                <form action="{{ route('tarefas.destroy', $tarefa->id) }}" method="POST" 
                                      onsubmit="return confirm('Excluir?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

#### **create.blade.php** (Criar)

```blade
@extends('layout.app')

@section('title', 'Nova Tarefa')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Nova Tarefa</h1>

        <form action="{{ route('tarefas.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="titulo" class="block font-semibold mb-2">Título</label>
                <input type="text" name="titulo" id="titulo" required class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label for="descricao" class="block font-semibold mb-2">Descrição</label>
                <textarea name="descricao" id="descricao" rows="4" class="w-full px-3 py-2 border rounded"></textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="concluida" class="mr-2">
                    <span>Marcar como concluída</span>
                </label>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tarefas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

#### **edit.blade.php** (Editar)

```blade
@extends('layout.app')

@section('title', 'Editar Tarefa')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Editar Tarefa</h1>

        <form action="{{ route('tarefas.update', $tarefa->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="titulo" class="block font-semibold mb-2">Título</label>
                <input type="text" name="titulo" value="{{ $tarefa->titulo }}" required class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label for="descricao" class="block font-semibold mb-2">Descrição</label>
                <textarea name="descricao" rows="4" class="w-full px-3 py-2 border rounded">{{ $tarefa->descricao }}</textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="concluida" {{ $tarefa->concluida ? 'checked' : '' }} class="mr-2">
                    <span>Marcar como concluída</span>
                </label>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tarefas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## 4. Sistema de Autenticação

### 4.1 Instalar Laravel Breeze (Autenticação Pronta)

```bash
# Instalar Breeze
composer require laravel/breeze --dev

# Instalar scaffolding (Blade + Tailwind)
php artisan breeze:install blade

# Instalar dependências e compilar
npm install
npm run dev

# Rodar migrations (cria tabela users)
php artisan migrate
```

**Pronto!** Agora você tem:
- ✅ Login: `/login`
- ✅ Registro: `/register`
- ✅ Recuperar senha: `/forgot-password`
- ✅ Dashboard: `/dashboard`

### 4.2 Proteger Rotas (Apenas Usuários Logados)

**Arquivo:** `routes/web.php`

```php
// Rotas públicas
Route::get('/', function () {
    return view('welcome');
});

// Rotas protegidas (precisa estar logado)
Route::middleware(['auth'])->group(function () {
    Route::resource('tarefas', TarefaController::class);
});
```

### 4.3 Adicionar Logout no Header

**Arquivo:** `resources/views/layout/app.blade.php`

```blade
<nav>
    <ul class="flex gap-2">
        @auth
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/tarefas">Tarefas</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            </li>
        @else
            <li><a href="/login">Login</a></li>
            <li><a href="/register">Registrar</a></li>
        @endauth
    </ul>
</nav>
```

---

## 5. Middleware e Níveis de Acesso

### 5.1 Adicionar Campo `role` na Tabela Users

**Criar migration:**

```bash
php artisan make:migration add_role_to_users_table
```

**Arquivo:** `database/migrations/xxxx_add_role_to_users_table.php`

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('user'); // user, admin, master
    });
}
```

**Rodar:**

```bash
php artisan migrate
```

### 5.2 Criar Middleware de Autorização

```bash
php artisan make:middleware CheckRole
```

**Arquivo:** `app/Http/Middleware/CheckRole.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verifica se o usuário está logado
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Verifica se o usuário tem uma das roles permitidas
        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Acesso negado!');
        }

        return $next($request);
    }
}
```

### 5.3 Registrar Middleware

**Arquivo:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### 5.4 Usar Middleware nas Rotas

**Arquivo:** `routes/web.php`

```php
// Apenas ADMIN pode acessar
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});

// ADMIN ou MASTER podem acessar
Route::middleware(['auth', 'role:admin,master'])->group(function () {
    Route::get('/relatorios', [RelatorioController::class, 'index']);
});

// Qualquer usuário logado
Route::middleware(['auth'])->group(function () {
    Route::resource('tarefas', TarefaController::class);
});
```

### 5.5 Verificar Role nas Views

```blade
@if(auth()->user()->role === 'admin')
    <a href="/admin">Painel Admin</a>
@endif

@if(in_array(auth()->user()->role, ['admin', 'master']))
    <a href="/relatorios">Relatórios</a>
@endif
```

---

## 6. Macetes e Dicas

### 6.1 Diferença entre Métodos do Controller

| Método | Ação | SQL | Recebe ID? |
|--------|------|-----|------------|
| `index()` | Listar todos | SELECT | ❌ |
| `create()` | Mostrar formulário de criação | - | ❌ |
| `store()` | Salvar novo registro | INSERT | ❌ |
| `edit($id)` | Mostrar formulário de edição | SELECT | ✅ |
| `update($id)` | Atualizar registro | UPDATE | ✅ |
| `destroy($id)` | Excluir registro | DELETE | ✅ |

### 6.2 Métodos HTTP nas Rotas

```blade
<!-- GET (listar/mostrar) -->
<a href="/tarefas">Ver Tarefas</a>

<!-- POST (criar) -->
<form action="/tarefas" method="POST">
    @csrf
    <!-- campos -->
</form>

<!-- PUT/PATCH (atualizar) -->
<form action="/tarefas/1" method="POST">
    @csrf
    @method('PUT')
    <!-- campos -->
</form>

<!-- DELETE (excluir) -->
<form action="/tarefas/1" method="POST">
    @csrf
    @method('DELETE')
    <button>Excluir</button>
</form>
```

### 6.3 Validação de Formulários

```php
$request->validate([
    'titulo' => 'required|max:255',           // Obrigatório, máx 255 chars
    'email' => 'required|email|unique:users', // Email único
    'senha' => 'required|min:8|confirmed',    // Mín 8 chars, confirmar
    'idade' => 'nullable|integer|min:18',     // Opcional, inteiro, mín 18
    'data' => 'required|date|after:today',    // Data após hoje
]);
```

### 6.4 Mensagens Flash (Feedback)

**No Controller:**

```php
return redirect()->route('tarefas.index')
    ->with('success', 'Tarefa criada com sucesso!');
```

**Na View:**

```blade
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

### 6.5 Relacionamentos (1 para N)

**Exemplo:** Um usuário tem várias tarefas

**Model User:**

```php
public function tarefas()
{
    return $this->hasMany(Tarefa::class);
}
```

**Model Tarefa:**

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

**Usar:**

```php
// Pegar tarefas do usuário
$tarefas = auth()->user()->tarefas;

// Pegar usuário da tarefa
$usuario = $tarefa->user;
```

### 6.6 Comandos Úteis

```bash
# Criar Model + Migration + Controller de uma vez
php artisan make:model Tarefa -mc

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver todas as rotas
php artisan route:list

# Criar seeder (dados fake)
php artisan make:seeder TarefaSeeder

# Rodar seeders
php artisan db:seed

# Resetar banco e rodar migrations + seeders
php artisan migrate:fresh --seed
```

### 6.7 Estrutura de Projeto Organizada

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TarefaController.php
│   │   └── UserController.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── Tarefa.php
│   └── User.php

resources/
├── views/
│   ├── layout/
│   │   └── app.blade.php
│   ├── tarefas/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── auth/
│       ├── login.blade.php
│       └── register.blade.php
├── css/
│   └── app.css
└── js/
    └── app.js

routes/
└── web.php

database/
├── migrations/
└── seeders/
```

---

## 🎯 Checklist Final

- [ ] Projeto Laravel criado
- [ ] Banco de dados configurado (.env)
- [ ] Migration criada e rodada
- [ ] Model criado com $fillable
- [ ] Controller com 6 métodos (index, create, store, edit, update, destroy)
- [ ] Rotas configuradas (web.php)
- [ ] Views criadas (index, create, edit)
- [ ] Layout base (app.blade.php)
- [ ] CSS configurado
- [ ] Autenticação instalada (Breeze)
- [ ] Middleware de role criado
- [ ] Rotas protegidas por auth e role

---

## 📚 Recursos Adicionais

- **Documentação Laravel:** https://laravel.com/docs
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Laravel Breeze:** https://laravel.com/docs/starter-kits#breeze

---
Com certeza, Ademar! Vamos consolidar tudo o que discutimos — desde a configuração inicial até a segurança de dados e relacionamentos — em um único arquivo Markdown mestre.

Este guia foi estruturado para ser o seu "mapa de bolso" na IntalenTI e nos seus estudos de Engenharia de Software.

---

# 🚀 Manual Mestre: Laravel Full Stack (CRUD ao Auth)

Este guia cobre a criação de um sistema robusto, garantindo que cada usuário gerencie apenas seus próprios dados.

## 📂 1. Estrutura de Pastas Sugerida

Para manter o projeto limpo (Clean Code):

* `app/Models`: Regras de negócio e relacionamentos.
* `app/Http/Controllers`: Intermédio entre banco e visão.
* `resources/views/layout`: Templates base (Blade).
* `resources/views/tarefas`: Telas específicas do CRUD.

---

## ⚙️ 2. Setup e Banco de Dados

### 2.1 Criar Model, Migration e Controller (O Combo)

```bash
# -m (migration), -c (controller), -r (resource com métodos prontos)
php artisan make:model Tarefa -mcr

```

### 2.2 Migration com Relacionamento

No arquivo `database/migrations/xxxx_create_tarefas_table.php`:

```php
public function up() {
    Schema::create('tarefas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Dono da tarefa
        $table->string('titulo');
        $table->text('descricao')->nullable();
        $table->boolean('concluida')->default(false);
        $table->timestamps();
    });
}

```

---

## 🧠 3. Camada de Dados (Model)

No arquivo `app/Models/Tarefa.php`:

```php
protected $fillable = ['titulo', 'descricao', 'concluida', 'user_id'];

protected $casts = [ 'concluida' => 'boolean' ];

// Relacionamento: Tarefa pertence a um Usuário
public function user() {
    return $this->belongsTo(User::class);
}

```

---

## 🕹️ 4. O Coração: TarefaController

Aqui aplicamos o filtro de segurança para o usuário logado.

```php
public function index() {
    // Pega APENAS as tarefas do usuário logado
    $tarefas = auth()->user()->tarefas; 
    return view('tarefas.index', compact('tarefas'));
}

public function store(Request $request) {
    $request->validate(['titulo' => 'required|max:255']);

    // Cria vinculado ao usuário da sessão
    auth()->user()->tarefas()->create($request->all());

    return redirect()->route('tarefas.index')->with('success', 'Criado!');
}

public function edit(Tarefa $tarefa) {
    // Bloqueio de segurança manual
    if ($tarefa->user_id !== auth()->id()) {
        abort(403, 'Acesso negado!');
    }
    return view('tarefas.edit', compact('tarefa'));
}

```

---

## 🌐 5. Rotas Protegidas

No arquivo `routes/web.php`:

```php
use App\Http\Controllers\TarefaController;

// Agrupa rotas que exigem login
Route::middleware(['auth'])->group(function () {
    Route::resource('tarefas', TarefaController::class);
});

```

---

## 🎨 6. Front-end & Blade Macetes

### 6.1 Layout Base (`layout/app.blade.php`)

```blade
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav>
        @auth
            <span>Olá, {{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST"> @csrf <button>Sair</button> </form>
        @endauth
    </nav>
    @yield('content')
</body>

```

### 6.2 Loops Inteligentes

```blade
@foreach($tarefas as $tarefa)
    <p>{{ $tarefa->titulo }}</p>
@empty
    <p>Você não tem tarefas pendentes. ☕</p>
@endforeach

```

---

## 🛠️ 7. Comandos de "Sobrevivência" (Terminal)

| Comando | Utilidade |
| --- | --- |
| `npm run dev` | Ativa o Vite (CSS/JS não funcionam sem isso). |
| `php artisan tinker` | Terminal interativo para testar o banco sem usar o navegador. |
| `php artisan migrate:fresh --seed` | Apaga tudo, recria o banco e roda dados de teste. |
| `php artisan route:list` | Lista todas as URLs do sistema para conferência. |

---

**Dica para baixar:** Como sou uma IA, você pode simplesmente **copiar o código acima**, abrir o seu VS Code, criar um arquivo chamado `MEU_GUIA.md` e colar.

Gostaria que eu detalhasse como criar um **Seeder** para você popular esse banco com 50 tarefas de teste em um segundo? Seria ótimo para testar a paginação!



🧩 Manual de Programação "Quebra-Cabeça" - TalentiPro
E aí! 🚀 Você pediu algo direto ao ponto, certo? Aqui está o seu Manual de Sobrevivência. Pense no código como peças de LEGO. Você só precisa saber qual peça encaixa onde.

🏗️ 1. O Modelo (A Planta da Casa)
Sempre comece pelo Model. Ele é a planta baixa. Se estiver errado, a casa cai.

Arquivo: app/Models/SuaFeature.php

php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SuaFeature extends Model
{
    // PEÇA 1: Quais campos posso salvar no banco? (Proteção contra Mass Assignment)
    protected $fillable = [
        'nome',
        'descricao',
        'status',
        'empresa_id', // <-- Sempre lembre de vincular à empresa se for multi-tenant
        'user_id'     // <-- Quem criou?
    ];
    // PEÇA 2: Relacionamentos (Quem é pai e quem é filho?)
    
    // "Eu pertenço a uma Empresa"
    public function empresa() {
        return $this->belongsTo(Empresa::class);
    }
    // "Eu tenho muitos Itens"
    public function itens() {
        return $this->hasMany(ItemFeature::class);
    }
}
🎮 2. O Controller (O Cérebro)
Aqui é onde você sofre, né? Vamos quebrar o Controller em 5 Blocos Padrão. Use isso para TUDO.

Arquivo: app/Http/Controllers/Web/SuaFeatureWebController.php

🧱 Bloco 1: Listar (index)
php
public function index() {
    // PEÇA 1: Segurança (Porteiro)
    // Quem entra aqui? Se não for 'admin' ou 'master', cai fora.
    if (!in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'gestor'])) {
        abort(403, 'Sem permissão, chefia.');
    }
    // PEÇA 2: Filtragem Inteligente (O "Pulo do Gato")
    // Se for Admin/Master, vê tudo. Se for Gestor, vê só o dele.
    $query = SuaFeature::query();
    if (auth()->user()->perfil_acesso == 'gestor') {
        $query->where('user_id', auth()->id());
    }
    // PEÇA 3: Pegar os dados
    $dados = $query->get(); // Ou ->paginate(10);
    // PEÇA 4: Mandar pra tela
    return view('sua_feature.index', compact('dados'));
}
🧱 Bloco 2: Salvar (store)
php
public function store(Request $request) {
    // PEÇA 1: Validação (O Fiscal)
    // Se passar daqui, os dados são confiáveis.
    $validado = $request->validate([
        'nome' => 'required|min:3',
        'status' => 'nullable',
        // Macete: Unique que ignora outros registros
        'codigo' => Rule::unique('sua_table')->where('empresa_id', auth()->user()->empresa_id)
    ]);
    // PEÇA 2: Preencher Automáticos (O Robô)
    // Coisas que o usuário não digita, mas o sistema precisa.
    $validado['empresa_id'] = auth()->user()->empresa_id;
    $validado['user_id'] = auth()->id();
    // PEÇA 3: Gravar no Banco
    SuaFeature::create($validado);
    // PEÇA 4: Redirecionar com Estilo
    return redirect()->route('sua_feature.index')
        ->with('success', 'Salvo com sucesso!');
}
🧱 Bloco 3: Editar (update)
php
public function update(Request $request, $id) {
    // PEÇA 1: Encontrar o Paciente
    $item = SuaFeature::findOrFail($id);
    // PEÇA 2: Segurança Extra (Dono da Bola)
    // Se for gestor, só pode editar o que ELE criou.
    if (auth()->user()->perfil_acesso == 'gestor' && $item->user_id != auth()->id()) {
        abort(403);
    }
    // PEÇA 3: Validação (Igual ao Store, mas ignorando o próprio ID no unique)
    $validado = $request->validate([
        'nome' => 'required',
        // Importante: Rule::unique(...)->ignore($id)
    ]);
    // PEÇA 4: Atualizar
    $item->update($validado);
    return back()->with('success', 'Atualizado!');
}
🎨 3. A View (A Vitrine)
No Front-end, o segredo é Não ter lógica complexa. Se você está fazendo muitas contas if na View, está errado. Faça no Controller.

Arquivo: resources/views/sua_feature/index.blade.php

🧩 Estrutura Base (Copie e Cole)
blade
{{-- PEÇA 1: Qual roupa eu visto? (Layout Dinâmico do TalentiPro) --}}
@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) 
    ? 'layouts.admin' 
    : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('content')
    {{-- PEÇA 2: Cabeçalho com Botão --}}
    <div class="header-actions">
        <h1>Minha Feature</h1>
        <button onclick="abrirModal()" class="btn-primary">Novo</button>
    </div>
    {{-- PEÇA 3: Feedback (Sucesso/Erro) --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    {{-- PEÇA 4: Tabela de Dados --}}
    <table>
        @forelse($dados as $d)
            <tr>
                <td>{{ $d->nome }}</td>
                <td>
                    {{-- PEÇA 5: Ações com Segurança Visual --}}
                    @if(in_array(auth()->user()->perfil_acesso, ['admin', 'master']))
                        <button onclick="editar({{ $d->id }})">Editar</button>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td>Nada encontrado.</td></tr>
        @endforelse
    </table>
    {{-- PEÇA 6: O JavaScript (Fetch API + URL Helper) --}}
    @push('scripts')
    <script>
        // MACETE SUPREMO: Nunca use URL fixa '/api/...', use {{ url(...) }}
        const API_URL = "{{ url('/api/sua-feature') }}";
        async function salvar() {
            // ... fetch(API_URL + '/store') ...
        }
    </script>
    @endpush
@endsection
🧪 4. Macetes do Dia a Dia (Cheat Sheet)
❓ Como debugo essa porcaria?
Não use echo ou print. Use:

php
dd($variavel); // "Dump and Die" - Mostra o valor e para tudo.
// ou
\Log::info('Passou aqui', ['dados' => $variavel]); // Grava no storage/logs/laravel.log
❓ O usuário logado?
php
auth()->id(); // ID numérico (ex: 15)
auth()->user(); // Objeto completo (ex: auth()->user()->email)
auth()->check(); // Retorna true se estiver logado
❓ Data e Hora (Carbon)?
php
now(); // Data atual
now()->format('d/m/Y'); // Formatada
\Carbon\Carbon::parse($data)->diffInDays(now()); // Diferença em dias
❓ Como faço uma rota nova?
No 
web.php
:

php
// Rota simples
Route::get('/meu-link', [MeuController::class, 'metodo'])->name('meu.link');
// Rota CRUD completa (index, create, store, show, edit, update, destroy)
Route::resource('meu-recurso', MeuController::class);
Resumo:

Crie o Model (Define o que salva).
Crie o Controller (Define quem pode fazer o que).
Crie a Rota (Define o endereço).
Crie a View (Mostra pro usuário).
Teste com dd() se der erro.
Boa sorte! 🚀