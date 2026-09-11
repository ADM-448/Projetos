<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoGoogle extends Model
{
    protected $table = 'configuracoes_google';
    
    protected $fillable = [
        'empresa_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
