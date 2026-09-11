<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }
}
