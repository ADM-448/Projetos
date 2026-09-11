<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscResponse extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function option()
    {
        return $this->belongsTo(DiscOption::class, 'disc_option_id');
    }
}
