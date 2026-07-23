<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password';
    protected $description = 'Reset admin password to default';

    public function handle()
    {
        // Try to find admin or master user
        $user = User::whereIn('perfil_acesso', ['admin', 'master'])->first();
        
        if (!$user) {
            $this->error('Admin/Master user not found!');
            return 1;
        }
        
        $user->password = Hash::make('password');
        $user->save();
        
        $this->info('Password reset successfully!');
        $this->info('Email: ' . $user->email);
        $this->info('Password: password');
        
        return 0;
    }
}
