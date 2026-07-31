<?php

namespace App\Models;

use App\Models\Vaga;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = ['nome','cidade','estado','email','site'];

    public function vagas(){
        return $this->hasMany(Vaga::class);
    }
}
