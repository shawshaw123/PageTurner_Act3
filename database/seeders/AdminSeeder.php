<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update admin user with known credentials
        $admin = User::updateOrCreate(
            ['email' => 'admin@pageturner.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@pageturner.com',
                'password' => Hash::make('admin123'), // Change this password!
                'role' => 'admin',
                'email_verified_at' => now(), // Auto-verify email
            ]
        );

        $this->command->info('Admin user created/updated:');
        $this->command->info('Email: admin@pageturner.com');
        $this->command->info('Password: admin123');
        $this->command->info('Email is already verified - you can now enable 2FA!');
    }
}
