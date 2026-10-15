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

class GerarPdiClimaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutos de timeout máximo

    protected string $dadosCompilados;
    protected int $gestorId;
    protected int $empresaId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $dadosCompilados, int $gestorId, int $empresaId)
    {
        $this->dadosCompilados = $dadosCompilados;
        $this->gestorId = $gestorId;
        $this->empresaId = $empresaId;
    }

    /**
     * Execute the job.
     */
    public function handle(AiManager $iaService): void
    {
        // 1. Chamar a IA para gerar o PDI
        $resultado = $iaService->gerarPdiClima($this->dadosCompilados);

        if (!$resultado['success']) {
            Log::error("[GerarPdiClimaJob] Erro da IA para empresa {$this->empresaId}: " . $resultado['error']);
            
            // Criar notificação de erro
            Atividade::create([
                'empresa_id' => $this->empresaId,
                'user_id' => $this->gestorId,
                'icone' => 'fa-robot',
                'cor' => 'danger',
                'titulo' => 'Erro ao Gerar PDI Organizacional com IA',
                'descricao' => "Ocorreu um erro ao gerar o PDI. Erro: " . $resultado['error'],
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
        $titulo = "[PDI Organizacional - IA] " . ($dadosPdi['titulo'] ?? 'Plano de Ação de Clima');

        $pdi = Pdi::create([
            'empresa_id' => $this->empresaId,
            'colaborador_id' => $this->gestorId, // Como é organizacional, fica atribuído ao Gestor que solicitou
            'gestor_id' => $this->gestorId,
            'titulo' => $titulo,
            'descricao' => $dadosPdi['descricao'] ?? null,
            'data_inicio' => now()->toDateString(),
            'data_fim' => $dadosPdi['prazo'] ?? null,
            'status' => StatusPdi::NAO_INICIADO,
            'progresso' => 0,
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
            'titulo' => 'PDI Organizacional Gerado',
            'descricao' => "O PDI Organizacional com base na Pesquisa de Clima foi gerado com sucesso. Clique para revisar.",
            'url' => route('pdis.edit', $pdi->id, false),
            'lida' => false,
        ]);
    }
}
