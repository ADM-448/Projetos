<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscAssessment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function responses()
    {
        return $this->hasMany(DiscResponse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPrimaryProfileBrAttribute()
    {
        $map = [
            'DOMINANCE' => 'Dominante',
            'INFLUENCE' => 'Influente',
            'STEADINESS' => 'Estável',
            'COMPLIANCE' => 'Cauteloso',
        ];
        return $map[$this->primary_profile] ?? $this->primary_profile;
    }

    public function getSecondaryProfileBrAttribute()
    {
        $map = [
            'DOMINANCE' => 'Dominante',
            'INFLUENCE' => 'Influente',
            'STEADINESS' => 'Estável',
            'COMPLIANCE' => 'Cauteloso',
        ];
        return $map[$this->secondary_profile] ?? $this->secondary_profile;
    }
}
