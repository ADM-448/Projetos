<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'descricao', 'salario', 'ativa', 'empresa_id', 'user_id'];

    protected $casts = [
        'ativa' => 'boolean',
        'salario' => 'decimal:2',
    ];

    /**
     * Regra de negócio: Vaga premium? Salário >= 10000.00
     */
    public function isPremium(): bool
    {
        return (float) $this->salario >= 10000.00;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
