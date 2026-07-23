<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Pdi, User, PdiAcao, PdiComentario, Notificacao};
use Illuminate\Http\Request;

class PdiWebController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Traz os PDIs com as ações e perfil completo do colab
        $query = Pdi::with(['colaborador.departamento', 'colaborador.cargo', 'colaborador.gestor', 'acoes', 'comentarios.user'])
            ->orderBy('created_at', 'desc');

        if ($user->perfil_acesso === 'gestor') {
            // Gestor vê PDIs de: Mesmo Departamento OU Subordinados Diretos (mesmo que outro setor)
            // Mas não deve ver o PRÓPRIO PDI na tela de Gestão (que é listado em 'Meu PDI')
            $query->whereHas('colaborador', function ($q) use ($user) {
                $q->where(function($sub) use ($user) {
                    $sub->where('departamento_id', $user->departamento_id)
                        ->orWhere('gestor_id', $user->id);
                })
                ->where('users.id', '!=', $user->id);
            });
            
            // E pode criar para: Mesmo Departamento OU Subordinados Diretos (exceto para ele mesmo)
            $colaboradores = User::with(['cargo'])
                ->where(function($q) use ($user) {
                    $q->where('departamento_id', $user->departamento_id)
                      ->orWhere('gestor_id', $user->id);
                })
                ->where('id', '!=', $user->id)
                ->get();
        } else {
            // Admin/Master vê tudo
            $colaboradores = User::with(['cargo'])->get();
        }

        $pdis = $query->get();

        return view('pdis.index', compact('pdis', 'colaboradores'));
    }

    public function meusPdis()
    {
        $userId = auth()->id();
        $user = auth()->user()->load(['departamento', 'cargo', 'gestor']); 

        // PDIs onde eu sou o colaborador
        $pdis = Pdi::where('colaborador_id', $userId)
            ->with(['acoes', 'comentarios.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pdis.meus_pdis', compact('pdis', 'user'));
    }

    public function store(Request $request)
    {
        // Validação
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'colaboradores' => 'required|array',
            'colaboradores.*' => 'exists:users,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'descricao' => 'nullable|string',
            'etapas' => 'nullable|array',
            'etapas.*' => 'required|string'
        ]);

        foreach ($request->colaboradores as $colabId) {
            $pdiData = [
                'titulo' => $data['titulo'],
                'colaborador_id' => $colabId,
                'gestor_id' => auth()->id(),
                'data_inicio' => $data['data_inicio'] ?? null,
                'data_fim' => $data['data_fim'] ?? null,
                'descricao' => $data['descricao'] ?? null,
                'status' => 'em_andamento',
                'progresso' => 0
            ];

            $pdi = Pdi::create($pdiData);

            // Notificacao ao Colaborador
            if ($pdiData['colaborador_id'] != auth()->id()) {
                Notificacao::create([
                    'empresa_id' => auth()->user()->empresa_id,
                    'destinatario_id' => $pdiData['colaborador_id'],
                    'remetente_id' => auth()->id(),
                    'tipo' => 'pdi',
                    'mensagem' => 'Um novo PDI foi atribuído a você: ' . $pdiData['titulo'],
                    'link' => route('pdis.meus')
                ]);
            }

            // Cria as Ações
            if (!empty($request->etapas)) {
                foreach ($request->etapas as $etapaTitulo) {
                    $pdi->acoes()->create([
                        'titulo' => $etapaTitulo,
                        'status' => 'pendente'
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'PDIs criados com sucesso!');
    }

    // Método AJAX simples para marcar/desmarcar tarefa
    public function toggleAcao($id)
    {
        $acao = PdiAcao::findOrFail($id);
        $novoStatus = $acao->status === 'concluido' ? 'pendente' : 'concluido';
        $acao->update(['status' => $novoStatus]);

        // Recalcula progresso do Pai
        $pdi = $acao->pdi;
        $total = $pdi->acoes()->count();
        $concluidos = $pdi->acoes()->where('status', 'concluido')->count();

        $progresso = ($total > 0) ? round(($concluidos / $total) * 100) : 0;
        
        $pdi->progresso = $progresso;
        
        // Define data_conclusao se 100%, senão null
        if ($progresso == 100) {
            $pdi->data_conclusao = now();
            $pdi->status = 'concluido';
        } else {
            $pdi->data_conclusao = null;
            $pdi->status = 'em_andamento';
        }
        
        $pdi->save();

        return response()->json(['success' => true, 'progresso' => $progresso]);
    }

    public function salvarComentario(Request $request, $id)
    {
        $request->validate(['comentario' => 'required|string']);
        
        PdiComentario::create([
            'pdi_id' => $id,
            'user_id' => auth()->id(),
            'comentario' => $request->comentario
        ]);

        return response()->json(['success' => true]);
    }

    public function updateGroup(Request $request) {
        $data = $request->validate([
            'old_titulo' => 'required|string',
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'colaboradores' => 'nullable|array',
            'colaboradores.*' => 'exists:users,id',
            'etapas' => 'nullable|array',
            'etapas.*' => 'nullable|string'
        ]);

        $oldTitulo = $request->old_titulo;
        $newTitulo = $data['titulo'];

        // 1. Atualizar Metadados (Título, Descrição, Datas) de todos existentes
        Pdi::where('titulo', $oldTitulo)->update([
            'titulo' => $newTitulo,
            'descricao' => $data['descricao'] ?? null,
            'data_inicio' => $data['data_inicio'] ?? null,
            'data_fim' => $data['data_fim'] ?? null,
        ]);

        // 2. Sincronizar Colaboradores
        $novosColabIds = $data['colaboradores'] ?? [];
        
        // IDs dos colaboradores que JÁ têm esse PDI (agora com Title atualizado)
        $pdisExistentes = Pdi::where('titulo', $newTitulo)->get();
        $colabsAtuais = $pdisExistentes->pluck('colaborador_id')->toArray();

        // A remover: Quem tinha e não tem mais na lista
        $toRemove = array_diff($colabsAtuais, $novosColabIds);
        if(!empty($toRemove)) {
            Pdi::where('titulo', $newTitulo)->whereIn('colaborador_id', $toRemove)->delete();
        }

        // A adicionar: Quem não tinha e agora tem
        $toAdd = array_diff($novosColabIds, $colabsAtuais);
        foreach($toAdd as $colabId) {
            $pdi = Pdi::create([
                'titulo' => $newTitulo,
                'colaborador_id' => $colabId,
                'gestor_id' => auth()->id(),
                'data_inicio' => $data['data_inicio'] ?? null,
                'data_fim' => $data['data_fim'] ?? null,
                'descricao' => $data['descricao'] ?? null,
                'status' => 'em_andamento',
                'progresso' => 0
            ]);

            // Cria as etapas para o novo PDI
            if (!empty($data['etapas'])) {
                foreach ($data['etapas'] as $etapa) {
                    if(!$etapa) continue;
                    $pdi->acoes()->create([
                        'titulo' => $etapa,
                        'status' => 'pendente'
                    ]);
                }
            }

             // Notificacao
             if ($colabId != auth()->id()) {
                Notificacao::create([
                    'empresa_id' => auth()->user()->empresa_id ?? 1,
                    'destinatario_id' => $colabId,
                    'remetente_id' => auth()->id(),
                    'tipo' => 'pdi',
                    'mensagem' => 'Um novo PDI foi atribuído a você: ' . $newTitulo,
                    'link' => route('pdis.meus')
                ]);
            }
        }

        // 3. Sincronizar Etapas (Para os PDIs que já existiam e continuam existindo)
        // Recarrega lista atualizada de PDIs (sem os removidos, com os adicionados já tratados no create, mas vamos garantir consistencia)
        // Na verdade, só precisamos processar os "Existentes mantidos", pois os novos já nasceram com as etapas certas.
        // Mas para simplificar, vamos rodar em TODOS com esse título (exceto os que acabamos de criar se quisermos otimizar, mas rodar em tudo garante consistência).
        
        $todasEtapas = array_filter($data['etapas'] ?? []);
        
        $pdisFinais = Pdi::where('titulo', $newTitulo)->get();

        foreach($pdisFinais as $pdi) {
            // A. Remove etapas que não existem mais na lista enviada
            // Cuidado: comparar strings. 
            // Se o usuário renomear uma etapa, aqui ela será deletada (status perdido) e recriada.
            // Para "Group Edit" simples, é o comportamento esperado. Soluções mais complexas exigiriam IDs.
            $pdi->acoes()->whereNotIn('titulo', $todasEtapas)->delete();

            // B. Adiciona etapas que faltam
            $acoesAtuais = $pdi->acoes->pluck('titulo')->toArray();
            foreach($todasEtapas as $etapa) {
                if(!in_array($etapa, $acoesAtuais)) {
                    $pdi->acoes()->create([
                        'titulo' => $etapa,
                        'status' => 'pendente'
                    ]);
                }
            }
            
            // Recalcula progresso (caso tenhamos removido algo concluido)
            $total = $pdi->acoes()->count();
            $concluidos = $pdi->acoes()->where('status', 'concluido')->count();
            $progresso = ($total > 0) ? round(($concluidos / $total) * 100) : 0;
            $pdi->update(['progresso' => $progresso]);
        }

        return redirect()->back()->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroyGroup(Request $request)
    {
        $titulo = $request->input('titulo');
        if(!$titulo) return redirect()->back()->with('error', 'Título inválido.');

        // Delete all PDIs with this title
        Pdi::where('titulo', $titulo)->delete();

        return redirect()->back()->with('success', 'Grupo excluído com sucesso!');
    }
    public function uploadComprovante(Request $request, $id)
    {
        $request->validate([
            'comprovante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $pdi = Pdi::findOrFail($id);

        if ($request->hasFile('comprovante')) {
            $file = $request->file('comprovante');
            $path = $file->store('pdi_docs', 'public');
            $pdi->comprovante = $path;
            $pdi->save();
        }

        return redirect()->back()->with('success', 'Comprovante enviado com sucesso!');
    }

    public function downloadComprovante($id)
    {
        $pdi = Pdi::findOrFail($id);

        if (!$pdi->comprovante) {
            abort(404, 'Nenhum comprovante anexado.');
        }

        $path = storage_path('app/public/' . $pdi->comprovante);

        if (!file_exists($path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        return response()->download($path);
    }
}
