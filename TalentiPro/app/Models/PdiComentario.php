<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdiComentario extends Model
{
    // A tabela correspondente é pdi_comentarios
    protected $table = 'pdi_comentarios';

    protected $fillable = [
        'pdi_id',
        'user_id',
        'comentario',
    ];

    /**
     * O comentário pertence a um PDI.
     */
    public function pdi(): BelongsTo
    {
        return $this->belongsTo(Pdi::class, 'pdi_id');
    }

    /**
     * O comentário pertence a um usuário (colaborador ou gestor).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
