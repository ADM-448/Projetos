<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class LeadImportService
{
    /**
     * Mapeamento de possíveis cabeçalhos para os campos padrão do Lead.
     */
    protected array $headerAliases = [
        'name' => ['nome', 'name', 'contato', 'lead', 'nome_completo', 'cliente'],
        'company_name' => ['empresa', 'company', 'company_name', 'nome_empresa', 'razao_social', 'organizacao'],
        'phone' => ['telefone', 'phone', 'celular', 'whatsapp', 'tel', 'fone', 'mobile', 'contato_tel'],
        'email' => ['email', 'e-mail', 'mail'],
    ];

    /**
     * Importa os leads a partir de um arquivo CSV/TXT enviado.
     */
    public function import(UploadedFile $file, int $tenantId, int $stageId, ?int $assignedUserId = null): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [
                'success' => false,
                'message' => 'Não foi possível ler o arquivo enviado.',
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        // Detecta o delimitador (, ou ;)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // Lê o cabeçalho
        $rawHeaders = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'O cabeçalho do arquivo está vazio ou inválido.',
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        // Mapeia colunas
        $columnMap = $this->resolveColumnMap($rawHeaders);

        if (! isset($columnMap['phone'])) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'Coluna de telefone/WhatsApp não encontrada. O arquivo deve ter uma coluna como "telefone" ou "whatsapp".',
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        $imported = 0;
        $skipped = 0;

        // Pré-carrega telefones já existentes no mesmo tenant para evitar duplicidade rápida
        $existingPhones = Lead::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->pluck('phone')
            ->map(fn ($p) => substr(preg_replace('/[^0-9]/', '', (string) $p), -10))
            ->filter()
            ->flip()
            ->toArray();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Ignora linhas vazias
            if (empty(array_filter($row))) {
                continue;
            }

            $leadData = [
                'tenant_id' => $tenantId,
                'pipeline_stage_id' => $stageId,
                'assigned_user_id' => $assignedUserId,
                'status' => 'open',
                'custom_attributes' => [],
            ];

            foreach ($row as $index => $value) {
                $trimmedVal = trim((string) $value);
                $targetField = $columnMap[$index] ?? null;

                if (! $targetField) {
                    continue;
                }

                if (in_array($targetField, ['name', 'company_name', 'phone', 'email'])) {
                    $leadData[$targetField] = $trimmedVal;
                } else {
                    // Campos adicionais vão para custom_attributes (ex: cargo, setor, cidade)
                    if ($trimmedVal !== '') {
                        $leadData['custom_attributes'][$targetField] = $trimmedVal;
                    }
                }
            }

            // Normalização do Telefone
            $rawPhone = $leadData['phone'] ?? '';
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

            // Valida tamanho mínimo de telefone
            if (strlen($cleanPhone) < 10) {
                $skipped++;
                continue;
            }

            // Adiciona DDI 55 (Brasil) se não estiver presente
            if (strlen($cleanPhone) === 10 || strlen($cleanPhone) === 11) {
                $cleanPhone = '55' . $cleanPhone;
            }

            // Checagem de duplicidade pelos últimos 10 dígitos
            $phoneKey = substr($cleanPhone, -10);
            if (isset($existingPhones[$phoneKey])) {
                $skipped++;
                continue;
            }

            $leadData['phone'] = $cleanPhone;
            $leadData['name'] = ! empty($leadData['name']) ? $leadData['name'] : 'Lead ' . substr($cleanPhone, -4);

            // Localiza ou cria a Empresa (Account) para agrupar os múltiplos contatos
            $companyName = ! empty($leadData['company_name']) ? trim($leadData['company_name']) : 'Empresa de ' . $leadData['name'];
            $company = \App\Models\Company::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $companyName,
                ],
                [
                    'pipeline_stage_id' => $stageId,
                    'assigned_user_id' => $assignedUserId,
                    'status' => 'open',
                ]
            );

            $leadData['company_id'] = $company->id;

            Lead::withoutGlobalScopes()->create($leadData);

            $existingPhones[$phoneKey] = true;
            $imported++;
        }

        fclose($handle);

        Log::info("[LeadImportService] Importação finalizada", [
            'tenant_id' => $tenantId,
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return [
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'message' => "Importação concluída! {$imported} leads cadastrados com sucesso ({$skipped} ignorados por duplicidade ou telefone inválido).",
        ];
    }

    /**
     * Mapeia os índices das colunas para os campos do sistema.
     */
    protected function resolveColumnMap(array $rawHeaders): array
    {
        $map = [];

        foreach ($rawHeaders as $index => $header) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-'], '_', $header))));

            $matchedField = null;
            foreach ($this->headerAliases as $field => $aliases) {
                if (in_array($normalized, $aliases)) {
                    $matchedField = $field;
                    break;
                }
            }

            // Se for um alias reconhecido, mapeia para o campo padrão; senão, usa o próprio nome da coluna como atributo customizado
            $map[$index] = $matchedField ?? $normalized;
            if ($matchedField) {
                $map[$matchedField] = true;
            }
        }

        return $map;
    }
}
