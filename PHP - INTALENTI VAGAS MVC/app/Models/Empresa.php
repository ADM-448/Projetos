<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_id',
        'nome_empresa',
        'nome_fantasia',
        'cnpj',
        'tipo', // 'empresa', 'prefeitura', 'rh'
        'dominio',
        'cidade',
        'estado',
        'logo_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_usuarios')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function vagas(): HasMany
    {
        return $this->hasMany(Vaga::class);
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopePrefeituras(Builder $query): Builder
    {
        return $query->where('tipo', 'prefeitura');
    }

    public function isPrefeitura(): bool
    {
        return $this->tipo === 'prefeitura';
    }
}
