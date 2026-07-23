<?php

namespace App\Http\Controllers\Web; // <--- O erro diz que não achava isso aqui

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Cargo;
use App\Models\User;
use Illuminate\Http\Request;

class ColaboradorWebController extends Controller
{
    public function index()
    {
        if (!in_array(auth()->user()->perfil_acesso, ['master', 'admin', 'rh'])) {
            abort(403, 'Acesso negado.');
        }

        // Lista todos os colaboradores com seus relacionamentos
        $colaboradores = User::with(['departamento', 'cargo', 'gestor'])
            ->where('id', '!=', auth()->id()) // Opcional: Não listar a si mesmo
            ->get();

        return view('colaboradores.index', compact('colaboradores'));
    }

    public function create()
    {
        if (!in_array(auth()->user()->perfil_acesso, ['master', 'admin', 'rh'])) {
            abort(403, 'Acesso negado.');
        }

        $departamentos = Departamento::all();
        $cargos = Cargo::all();
        // Gestores podem ser qualquer usuário com perfil de liderança
        $gestores = User::whereIn('perfil_acesso', ['gestor', 'master', 'admin', 'rh', 'mediador'])->get();

        return view('colaboradores.form', compact('departamentos', 'cargos', 'gestores'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->perfil_acesso, ['master', 'admin', 'rh'])) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Validação de Unique composta (Email + Empresa)
            'email' => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users')->where(function ($query) {
                    return $query->where('empresa_id', auth()->user()->empresa_id);
                })
            ],
            'password' => 'required|min:6', // Senha obrigatória na criação
            'cpf' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date',
            'genero' => 'nullable|string',
            'escolaridade' => 'nullable|string',
            'data_admissao' => 'nullable|date',
            'status' => 'nullable|string',
            'vinculo' => 'nullable|string',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'perfil_acesso' => 'required|string',
        ]);

        // Limpa máscara do telefone antes de salvar
        if (!empty($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        }

        // BLOQUEIO DE SEGURANÇA: Impede a criação de novos Admins (mas permite Master)
        if ($validated['perfil_acesso'] === 'admin') {
            return back()->withErrors(['perfil_acesso' => 'A criação de novos administradores está bloqueada por segurança.']);
        }

        // Criptografa a senha antes de salvar
        $validated['password'] = bcrypt($validated['password']);

        // Se tivesse empresa_id, seria aqui: $validated['empresa_id'] = 1;

        User::create($validated);

        return redirect()->route('colaboradores.index')->with('success', 'Colaborador criado com sucesso!');
    }

    public function show($id)
    {
        $colaborador = User::with(['departamento', 'cargo', 'gestor'])->findOrFail($id);
        return view('colaboradores.show', compact('colaborador'));
    }

    public function edit($id)
    {
        // Só pode editar a si mesmo ou ser admin/master/rh
        if ($id != auth()->id() && !in_array(auth()->user()->perfil_acesso, ['master', 'admin', 'rh'])) {
            abort(403, 'Acesso negado.');
        }

        $colaborador = User::findOrFail($id);
        $departamentos = Departamento::all();
        $cargos = Cargo::all();
        $gestores = User::whereIn('perfil_acesso', ['gestor', 'master', 'admin', 'rh', 'mediador'])
            ->where('id', '!=', $id) // Não pode ser gestor de si mesmo
            ->get();

        return view('colaboradores.form', compact('colaborador', 'departamentos', 'cargos', 'gestores'));
    }

    public function update(Request $request, $id)
    {
        $colaborador = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Permite email repetido desde que seja em outra empresa (unique compuesto)
            'email' => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('empresa_id', auth()->user()->empresa_id);
                })->ignore($id)
            ],
            'password' => 'nullable|min:6', // Senha opcional na edição
            'cpf' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date',
            'genero' => 'nullable|string',
            'escolaridade' => 'nullable|string',
            'data_admissao' => 'nullable|date',
            'status' => 'nullable|string',
            'vinculo' => 'nullable|string',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'gestor_id' => 'nullable|exists:users,id',
            'perfil_acesso' => 'required|string',
        ]);

        // Limpa máscara do telefone antes de salvar
        if (!empty($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        } else {
            $validated['telefone'] = null;
        }

        // Lógica de senha: só atualiza se o usuário digitou algo novo
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); // Remove do array para não salvar em branco
        }

        $colaborador->update($validated);

        $colaborador->update($validated);

        // Se o usuário estiver editando o próprio perfil e não for admin/master na tela de gestão,
        // ou se for um perfil que não deve acessar a lista geral, redireciona de volta para "Minha Conta".
        if ($id == auth()->id() || auth()->user()->perfil_acesso == 'colaborador' || request()->routeIs('perfil.update')) {
             return redirect()->route('perfil.index')->with('success', 'Seus dados foram atualizados!');
        }

        return redirect()->route('colaboradores.index')->with('success', 'Colaborador atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->perfil_acesso, ['master', 'admin', 'rh'])) {
            abort(403, 'Acesso negado.');
        }

        $colaborador = User::findOrFail($id);
        $colaborador->delete();

        return redirect()->route('colaboradores.index')->with('success', 'Colaborador excluído.');
    }

    // Métodos extras para as telas de "Contas" e "Perfil"
    public function contas()
    {
        // A tela de contas lista os usuários para gerenciamento de acesso
        $users = User::paginate(20);
        return view('colaboradores.contas', compact('users'));
    }

    public function meuPerfil()
    {
        $colaborador = auth()->user()->load(['departamento', 'cargo', 'gestor']);
        
        $departamentos = Departamento::all();
        $cargos = Cargo::all();
        $gestores = User::whereIn('perfil_acesso', ['gestor', 'master', 'admin', 'rh', 'mediador'])
            ->where('id', '!=', $colaborador->id)
            ->get();

        return view('colaboradores.perfil', compact('colaborador', 'departamentos', 'cargos', 'gestores'));
    }
    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('arquivo');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Detect delimiter
        $line = fgets($handle);
        $delimiter = (strpos($line, ';') !== false) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        
        if (!$header) {
             return back()->withErrors(['arquivo' => 'Arquivo vazio ou inválido.']);
        }

        // Remove BOM if present
        if (isset($header[0]) && strncmp($header[0], "\xEF\xBB\xBF", 3) === 0) {
            $header[0] = substr($header[0], 3);
        }

        // Normalize headers
        $header = array_map(function($h) {
            return strtolower(trim($h)); 
        }, $header);

        // DEBUG: Log the actual headers
        \Log::info('CSV Headers:', $header);

        // Map indices
        $idxName = array_search('nome', $header);
        $idxEmail = array_search('email', $header);
        $idxCpf = array_search('cpf', $header);
        $idxTelefone = array_search('telefone', $header);
        $idxPerfil = array_search('perfil', $header);
        $idxCargo = array_search('cargo', $header);
        $idxDept = array_search('departamento', $header);
        $idxPass = array_search('senha', $header);

        if ($idxName === false || $idxEmail === false) {
             fclose($handle);
             $headerStr = implode(', ', $header);
             return back()->withErrors(['arquivo' => "Cabeçalho inválido. Encontrado: [{$headerStr}]. Esperado: 'nome' e 'email'."]);
        }

        $skipped = [];
        $importedCount = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
             if(empty($row[$idxName])) continue; // Skip empty rows

             $email = trim($row[$idxEmail]);
             if (empty($email)) continue;

             // Find or Create Dept
             $deptId = null;
             if ($idxDept !== false && !empty($row[$idxDept])) {
                 $dName = trim($row[$idxDept]);
                 $dept = Departamento::firstOrCreate(['nome' => $dName]);
                 $deptId = $dept->id;
             }

             // Find or Create Cargo
             $cargoId = null;
             if ($idxCargo !== false && !empty($row[$idxCargo])) {
                 $cName = trim($row[$idxCargo]);
                 $cargo = Cargo::firstOrCreate(['nome' => $cName, 'departamento_id' => $deptId]);
                 $cargoId = $cargo->id;
             }
             
             $perfil = ($idxPerfil !== false && !empty($row[$idxPerfil])) ? strtolower(trim($row[$idxPerfil])) : 'colaborador';
             // Map visual names 
             // if($perfil == 'administrador') $perfil = 'admin'; // REMOVIDO: Admin não pode ser importado via CSV
             
             // BLOQUEIO: Se tentarem importar Admin, vira Colaborador (Mas permite Master)
             if(in_array($perfil, ['admin', 'administrador'])) {
                 $perfil = 'colaborador';
             }

             if (!in_array($perfil, ['master', 'gestor', 'mediador', 'colaborador'])) {
                 $perfil = 'colaborador';
             }

             $userData = [
                 'name' => trim($row[$idxName]),
                 'email' => $email,
                 'cpf' => ($idxCpf !== false && isset($row[$idxCpf])) ? trim($row[$idxCpf]) : null,
                 'telefone' => ($idxTelefone !== false && isset($row[$idxTelefone]) && trim($row[$idxTelefone]) !== '')
                     ? preg_replace('/\D/', '', trim($row[$idxTelefone]))
                     : null,
                 'perfil_acesso' => $perfil,
                 'departamento_id' => $deptId,
                 'cargo_id' => $cargoId,
             ];
             
             // Password
             if ($idxPass !== false && !empty($row[$idxPass])) {
                 $userData['password'] = bcrypt(trim($row[$idxPass]));
             }

             // Find existing user by email or CPF (IN CURRENT COMPANY)
             $user = User::where('email', $email)
                 ->orWhere(function($q) use ($userData) {
                     if (!empty($userData['cpf'])) {
                         $q->where('cpf', $userData['cpf']);
                     }
                 })
                 ->first();

             if ($user) {
                 // Update existing user in THIS company
                 $user->update($userData);
             } else {
             // Check globally if user exists (to prevent duplicate entry error) -> REMOVIDO: Agora permitimos duplicados por empresa
             // $existsGlobal = ... 

             // Create new user
             if (empty($userData['password'])) {
                 $userData['password'] = bcrypt('mudar123');
             }
             User::create($userData);
             $importedCount++;
         }
         }
        
        fclose($handle);

        $msg = "Importação concluída! {$importedCount} usuários importados/atualizados.";
        
        // Se houver usuários pulados, passa para a sessão em uma chave separada
        if (count($skipped) > 0) {
            return redirect()->back()
                ->with('success', $msg)
                ->with('import_errors', $skipped);
        }

        return redirect()->back()->with('success', $msg);
    }
}