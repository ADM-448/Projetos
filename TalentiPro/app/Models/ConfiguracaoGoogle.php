<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoGoogle extends Model
{
    protected $table = 'configuracoes_google';
    
    protected $fillable = [
        'empresa_id',
        'service_account_json',
        'drive_folder_id',
    ];
}
