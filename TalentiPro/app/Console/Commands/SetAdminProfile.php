<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetAdminProfile extends Command
{
    protected $signature = 'admin:set-profile';
    protected $description = 'Set user profile to admin';

    public function handle()
    {
        $user = User::where('email', 'admin@talentipro.com')->first();
        
        if (!$user) {
            $this->error('Admin user not found!');
            return 1;
        }
        
        $user->perfil_acesso = 'admin';
        $user->save();
        
        $this->info('Profile updated successfully!');
        $this->info('Email: ' . $user->email);
        $this->info('Profile: admin');
        
        return 0;
    }
}
