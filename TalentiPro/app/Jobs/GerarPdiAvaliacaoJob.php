<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\AI\AiManager;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\Atividade;
use App\Models\Empresa;
use App\Models\User;
use App\Enums\StatusPdi;
use Illuminate\Support\Facades\Log;

class GerarPdiAvaliacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutos de timeout máximo

    protected string $dadosCompilados;
    protected string $nomeColaborador;
    protected int $colaboradorId;
    protected int $gestorId;
    protected int $empresaId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $dadosCompilados, string $nomeColaborador, int $colaboradorId, int $gestorId, int $empresaId)
    {
        $this->dadosCompilados = $dadosCompilados;
        $this->nomeColaborador = $nomeColaborador;
        $this->colaboradorId = $colaboradorId;
        $this->gestorId = $gestorId;
        $this->empresaId = $empresaId;
    }

    /**
     * Execute the job.
     */
    public function handle(AiManager $iaService): void
    {
        // 1. Chamar a IA para gerar o PDI
        $resultado = $iaService->gerarPdiAvaliacao($this->dadosCompilados, $this->nomeColaborador);

        if (!$resultado['success']) {
            Log::error("[GerarPdiAvaliacaoJob] Erro da IA para colab {$this->colaboradorId}: " . $resultado['error']);
            
            // Criar notificação de erro
            Atividade::create([
                'empresa_id' => $this->empresaId,
                'user_id' => $this->gestorId,
                'icone' => 'fa-robot',
                'cor' => 'danger',
                'titulo' => 'Erro ao Gerar PDI com IA',
                'descricao' => "Ocorreu um erro ao gerar o PDI de {$this->nomeColaborador}. Erro: " . $resultado['error'],
                'url' => '#',
                'lida' => false,
            ]);
            return;
        }

        $dadosPdi = $resultado['dados'];

        // 2. Incrementar consumo de tokens/IA na Empresa
        $empresa = Empresa::find($this->empresaId);
        if ($empresa) {
            $empresa->increment('ia_transcricoes_utilizadas');
        }

        // 3. Salvar o PDI no Banco de Dados
        // Como o status de Rascunho não existe no Enum, usaremos NAO_INICIADO
        // e colocaremos "[Rascunho IA]" no título para o gestor identificar facilmente.
        $titulo = "[Rascunho IA] " . ($dadosPdi['titulo'] ?? 'Plano de Desenvolvimento');

        // Mantém só o último PDI de IA do colaborador (concluídos e manuais são preservados)
        Pdi::removerPdisIaAnteriores($this->colaboradorId);

        $pdi = Pdi::create([
            'empresa_id' => $this->empresaId,
            'colaborador_id' => $this->colaboradorId,
            'gestor_id' => $this->gestorId,
            'titulo' => $titulo,
            'descricao' => $dadosPdi['descricao'] ?? null,
            'data_inicio' => now()->toDateString(),
            'data_fim' => $dadosPdi['prazo'] ?? null,
            'status' => StatusPdi::NAO_INICIADO,
            'progresso' => 0,
            'gerado_ia' => true,
        ]);

        // 4. Salvar as ações vinculadas
        if (isset($dadosPdi['acoes']) && is_array($dadosPdi['acoes'])) {
            foreach ($dadosPdi['acoes'] as $acao) {
                if (!empty($acao['titulo'])) {
                    PdiAcao::create([
                        'pdi_id' => $pdi->id,
                        'titulo' => $acao['titulo'],
                        'descricao' => $acao['descricao'] ?? null,
                        'concluida' => false,
                    ]);
                }
            }
        }

        // 5. Criar a notificação (Sininho) para o Gestor/Master informando que está pronto
        Atividade::create([
            'empresa_id' => $this->empresaId,
            'user_id' => $this->gestorId,
            'icone' => 'fa-wand-magic-sparkles',
            'cor' => 'primary',
            'titulo' => 'PDI Gerado com IA',
            'descricao' => "O PDI para o colaborador {$this->nomeColaborador} foi gerado com sucesso. Clique aqui para revisar.",
            'url' => route('pdis.edit', $pdi->id, false),
            'lida' => false,
        ]);
    }
}
