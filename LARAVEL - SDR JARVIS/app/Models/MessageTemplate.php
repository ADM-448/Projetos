<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'category',
        'channel',
        'content',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];
}
