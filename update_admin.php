<?php

// Update admin user with proper credentials
$admin = \App\Models\User::where('email', 'admin@pageturner.com')->first();

if ($admin) {
    $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
    $admin->email_verified_at = now();
    $admin->save();
    
    echo "Admin updated successfully!\n";
    echo "Email: admin@pageturner.com\n";
    echo "Password: admin123\n";
    echo "Email verified: Yes\n";
} else {
    echo "Admin user not found. Creating new one...\n";
    
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@pageturner.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    
    echo "Admin created successfully!\n";
    echo "Email: admin@pageturner.com\n";
    echo "Password: admin123\n";
    echo "Email verified: Yes\n";
}
