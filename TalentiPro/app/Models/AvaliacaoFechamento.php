<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoFechamento extends Model
{
    use HasFactory;

    protected $table = 'avaliacao_fechamentos';

    protected $fillable = [
        'avaliacao_id',
        'avaliado_id',
        'responsavel_id',
        'nota_final',
        'comentario',
    ];

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function avaliado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
