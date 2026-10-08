<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'document',
        'status',
        'settings',
        'voip_enabled',
        'voip_server_host',
        'voip_webrtc_ws_url',
        'voip_ami_host',
        'voip_ami_port',
        'voip_ami_user',
        'voip_ami_secret',
        'voip_recordings_url',
    ];

    protected $casts = [
        'settings' => 'array',
        'voip_enabled' => 'boolean',
        'voip_ami_port' => 'integer',
    ];

    /**
     * Retorna as configurações e instruções de IA do perfil de CRM (ou padrões inteligentes).
     */
    public function getAiConfig(): array
    {
        $settings = $this->settings ?? [];

        return [
            'ai_name' => $settings['ai_name'] ?? 'JARVIS SDR Agent',
            'ai_persona' => $settings['ai_persona'] ?? 'Consultor Comercial Sênior B2B focado em qualificação e agendamento de reuniões',
            'ai_model' => $settings['ai_model'] ?? 'gpt-4o-mini',
            'ai_prompt' => $settings['ai_prompt'] ?? 'Você é o SDR da operação comercial. Seu objetivo principal é gerar conexão com o decisor, mapear dores e conduzir para uma reunião de 15 minutos com o executivo da conta.',
            'company_value_prop' => $settings['company_value_prop'] ?? 'Plataforma inteligente de prospecção e vendas B2B que acelera o contato com decisores e aumenta a taxa de reuniões marcadas em até 3x.',
            'instructions' => $settings['instructions'] ?? "1. Sempre trate o lead pelo primeiro nome de forma calorosa e profissional.\n2. Nunca mande textos longos ou cansativos no primeiro contato.\n3. Se o lead fizer objeção de preço, reconheça a preocupação e ancore o valor antes de citar valores exatos.\n4. O objetivo primordial de cada conversa é agendar uma demonstração de 15 minutos.",
            'guardrails' => $settings['guardrails'] ?? "• Nunca prometa descontos sem validação da diretoria.\n• Nunca fale mal de concorrentes; destaque nossos pontos fortes.\n• Nunca envie PDF ou material pesado no WhatsApp sem o cliente pedir.\n• Se o cliente for agressivo, mantenha compostura profissional e desarmamento empático.",
            'faq' => $settings['faq'] ?? [
                ['q' => 'Quanto custa a plataforma?', 'a' => 'Nossos planos são dimensionados de acordo com o tamanho da equipe de vendas e o volume de prospecção. Em uma call de 15 minutos conseguimos mapear a melhor configuração para o seu modelo.'],
                ['q' => 'Como funciona a implantação?', 'a' => 'A implantação é assistida por um especialista e fica pronta em menos de 48 horas, incluindo integração de WhatsApp e telefonia.'],
                ['q' => 'Vocês integram com nosso CRM atual?', 'a' => 'Sim! O SDR JARVIS possui API aberta e integra com os principais CRMs de fechamento do mercado.'],
            ],
            'tone' => $settings['tone'] ?? 'consultative', // consultative | direct | friendly | formal
            'ai_api_key' => ! empty($settings['ai_api_key']) ? '••••••••' . substr($settings['ai_api_key'], -4) : null,
            'has_custom_key' => ! empty($settings['ai_api_key']),
            'temperature' => (float) ($settings['temperature'] ?? 0.7),
            // Modo Piloto Automático (Auto-SDR)
            'ai_autonomous_enabled' => (bool) ($settings['ai_autonomous_enabled'] ?? false),
            'autonomous_delay_seconds' => (int) ($settings['autonomous_delay_seconds'] ?? 8),
            'autonomous_auto_advance' => (bool) ($settings['autonomous_auto_advance'] ?? true),
            'autonomous_auto_disqualify' => (bool) ($settings['autonomous_auto_disqualify'] ?? true),
            'autonomous_handoff_on_stress' => (bool) ($settings['autonomous_handoff_on_stress'] ?? true),
            'autonomous_working_hours_only' => (bool) ($settings['autonomous_working_hours_only'] ?? false),
            'autonomous_working_hours_start' => $settings['autonomous_working_hours_start'] ?? '08:00',
            'autonomous_working_hours_end' => $settings['autonomous_working_hours_end'] ?? '18:30',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pipelines(): HasMany
    {
        return $this->hasMany(Pipeline::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function cadences(): HasMany
    {
        return $this->hasMany(Cadence::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(WhatsappInstance::class);
    }

    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }
}
