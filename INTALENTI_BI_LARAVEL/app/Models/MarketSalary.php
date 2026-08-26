<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'cargo',
        'regiao',
        'status', // 'pending', 'completed', 'failed'
        'mediana',
        'piso',
        'teto',
        'raw_data',
        'last_scraped_at',
    ];

    protected $casts = [
        'mediana' => 'decimal:2',
        'piso' => 'decimal:2',
        'teto' => 'decimal:2',
        'raw_data' => 'array',
        'last_scraped_at' => 'datetime',
    ];
}
