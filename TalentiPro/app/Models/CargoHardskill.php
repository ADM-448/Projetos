<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoHardskill extends Model
{
    protected $fillable = [
        'cargo_id',
        'nome',
    ];

    /**
     * Cargo ao qual esta hardskill pertence
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
