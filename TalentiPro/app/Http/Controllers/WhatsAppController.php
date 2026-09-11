<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessarFilaWhatsApp;
use App\Models\WhatsappQueue;
use App\Models\Empresa;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Painel de configuração e monitoramento do WhatsApp.
     * Exibe: status da instância, QR Code, estatísticas da fila.
     */
    public function index(Request $request, WhatsAppService $wpp)
    {
        $user = auth()->user();

        if (!in_array($user->perfil_acesso->value, ['admin', 'master'])) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar as configurações do WhatsApp.');
        }
        
        if ($user->perfil_acesso->value === 'master') {
            if (!$user->empresa || !$user->empresa->whatsapp_ativo) {
                return redirect()->route('dashboard')->with('error', 'Sua empresa não possui a API de WhatsApp liberada. Contate o suporte.');
            }
        }

        $empresas = collect();

        // Se for admin global, carrega todas as empresas para permitir seleção
        if ($user->perfil_acesso->value === 'admin') {
            $empresas = Empresa::orderBy('nome')->get();
            $empresaId = $request->query('empresa_id') ?? $user->empresa_id ?? ($empresas->first()?->id ?? 1);
        } else {
            // Se for master, escopa à própria empresa
            $empresaId = $user->empresa_id;
        }

        // Define a empresa ativa na Evolution API
        $wpp->setEmpresaId($empresaId);

        $status      = $wpp->status();
        $qrCode      = $wpp->qrCode();
        $enabled     = $wpp->isEnabled();
        $isConectado = $wpp->estaConectado();

        // Estatísticas da fila isoladas por empresa
        $filaQuery = WhatsappQueue::whereHas('user', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        });

        $filaPendentes = (clone $filaQuery)->where('status', 'pendente')
                            ->whereColumn('tentativas', '<', 'max_tentativas')
                            ->count();
                            
        $filaFalhadas  = (clone $filaQuery)->where('status', 'falhou')->count();
        
        $filaEnviadas  = (clone $filaQuery)->where('status', 'enviada')
                            ->where('enviada_em', '>=', now()->subDay())
                            ->count();

        $ultimasFalhadas = (clone $filaQuery)->where('status', 'falhou')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();

        $empresaSelecionada = $user->perfil_acesso->value === 'admin' ? Empresa::find($empresaId) : $user->empresa;

        return view('configuracoes.whatsapp', compact(
            'status', 'qrCode', 'enabled', 'isConectado',
            'filaPendentes', 'filaFalhadas', 'filaEnviadas', 'ultimasFalhadas',
            'empresas', 'empresaId', 'empresaSelecionada'
        ));
    }

    /**
     * Envia mensagem de teste para o WhatsApp do usuário logado.
     */
    public function teste(Request $request, WhatsAppService $wpp)
    {
        $user = auth()->user();

        if (!in_array($user->perfil_acesso->value, ['admin', 'master'])) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para enviar testes do WhatsApp.');
        }
        
        if ($user->perfil_acesso->value === 'master') {
            if (!$user->empresa || !$user->empresa->whatsapp_ativo) {
                return redirect()->route('dashboard')->with('error', 'Sua empresa não possui a API de WhatsApp liberada.');
            }
        }
        
        if ($user->perfil_acesso->value === 'admin') {
            $empresaId = $request->input('empresa_id') ?? $user->empresa_id ?? 1;
        } else {
            $empresaId = $user->empresa_id;
        }

        $wpp->setEmpresaId($empresaId);

        if (empty($user->telefone)) {
            return back()->with('error', '⚠️ Você não tem WhatsApp cadastrado no perfil. Edite seu cadastro e adicione o número.');
        }

        $empresaNome = Empresa::find($empresaId)?->nome ?? 'N/A';

        $enviado = $wpp->enviar(
            $user->telefone,
            implode("\n", [
                '✅ *Teste de integração TalentiPro*',
                '',
                "Empresa: *{$empresaNome}*",
                'Se você recebeu esta mensagem, a integração está funcionando!',
                '',
                '🚀 _TalentiPro — Sistema de Gestão de Pessoas_',
            ]),
            $user->id
        );

        if ($enviado) {
            return back()->with('success', '✅ Mensagem de teste enviada! Verifique seu WhatsApp.');
        }

        return back()->with('error', '❌ Falha ao enviar. Mensagem salva na fila para reenvio automático. Verifique se a instância está conectada.');
    }

    /**
     * Reprocessa imediatamente as mensagens que falharam (esgotaram tentativas).
     */
    public function reprocessar(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->perfil_acesso->value, ['admin', 'master'])) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para reprocessar a fila do WhatsApp.');
        }
        
        if ($user->perfil_acesso->value === 'master') {
            if (!$user->empresa || !$user->empresa->whatsapp_ativo) {
                return redirect()->route('dashboard')->with('error', 'Sua empresa não possui a API de WhatsApp liberada.');
            }
        }
        
        if ($user->perfil_acesso->value === 'admin') {
            $empresaId = $request->input('empresa_id') ?? $user->empresa_id ?? 1;
        } else {
            $empresaId = $user->empresa_id;
        }

        $filaQuery = WhatsappQueue::whereHas('user', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->where('status', 'falhou');

        $total = $filaQuery->count();

        if ($total === 0) {
            return back()->with('success', 'Nenhuma mensagem falhada na fila desta empresa.');
        }

        // Reseta as falhadas para pendente (reinicia contagem de tentativas)
        $filaQuery->update([
            'status'     => 'pendente',
            'tentativas' => 0,
            'ultimo_erro'=> null,
        ]);

        // Despacha o job imediatamente
        ProcessarFilaWhatsApp::dispatch();

        return back()->with('success', "🔄 {$total} mensagem(ns) reiniciada(s) e sendo processadas. Aguarde alguns instantes e atualize a página.");
    }
}
