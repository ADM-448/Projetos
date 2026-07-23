<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdiComentario extends Model
{
    use HasFactory;
    
    protected $table = 'pdi_comentarios';

    protected $fillable = [
        'pdi_id',
        'user_id',
        'comentario',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
