<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidatura_id',
        'vaga_id',
        'candidato_user_id',
        'recrutador_id',
    ];

    public function candidatura(): BelongsTo
    {
        return $this->belongsTo(Candidatura::class);
    }

    public function vaga(): BelongsTo
    {
        return $this->belongsTo(Vaga::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidato_user_id');
    }

    public function recrutador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recrutador_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(MensagemChat::class)->orderBy('created_at');
    }
}
