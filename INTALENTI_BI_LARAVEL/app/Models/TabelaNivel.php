<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaNivel extends Model
{
    protected $fillable = [
        'cargo_id',
        'nivel',
        'senioridade',
        'valor',
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
