<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappQueue extends Model
{
    protected $table = 'whatsapp_queues';

    protected $fillable = [
        'user_id',
        'numero',
        'mensagem',
        'status',
        'tentativas',
        'max_tentativas',
        'ultimo_erro',
        'enviada_em',
    ];

    protected $casts = [
        'enviada_em' => 'datetime',
    ];

    // ─── Scopes ────────────────────────────────────────────

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente')
                     ->whereColumn('tentativas', '<', 'max_tentativas');
    }

    public function scopeFalhadas($query)
    {
        return $query->where('status', 'falhou');
    }

    // ─── Helpers ───────────────────────────────────────────

    public function marcarEnviada(): void
    {
        $this->update([
            'status'     => 'enviada',
            'enviada_em' => now(),
            'ultimo_erro'=> null,
        ]);
    }

    public function registrarFalha(string $erro): void
    {
        $novasTentativas = $this->tentativas + 1;
        $esgotou = $novasTentativas >= $this->max_tentativas;

        $this->update([
            'tentativas'  => $novasTentativas,
            'status'      => $esgotou ? 'falhou' : 'pendente',
            'ultimo_erro' => $erro,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
