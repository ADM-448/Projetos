<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'competencia_id',
        'nome',
    ];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class);
    }
}
