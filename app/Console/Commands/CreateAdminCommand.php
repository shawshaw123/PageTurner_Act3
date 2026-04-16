<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create or update admin account';

    public function handle()
    {
        $email = 'admin@pageturner.com';
        $password = 'admin123';
        
        $admin = User::where('email', $email)->first();
        
        if ($admin) {
            $admin->password = Hash::make($password);
            $admin->email_verified_at = now();
            $admin->save();
            
            $this->info('Admin account updated successfully!');
        } else {
            User::create([
                'name' => 'Admin User',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            
            $this->info('Admin account created successfully!');
        }
        
        $this->info('Email: ' . $email);
        $this->info('Password: ' . $password);
        $this->info('Email verified: Yes');
        $this->info('You can now enable 2FA!');
        
        return 0;
    }
}
