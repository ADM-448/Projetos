<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'icone',
        'cor',
        'titulo',
        'descricao',
        'url',
        'lida',
        'read_at'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
