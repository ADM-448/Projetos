<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembrete extends Model
{
    protected $fillable = [
        'user_id',
        'lembreteable_type',
        'lembreteable_id',
        'data_agendada',
        'mensagem',
        'status',
    ];

    protected $casts = [
        'data_agendada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lembreteable()
    {
        return $this->morphTo();
    }
}
