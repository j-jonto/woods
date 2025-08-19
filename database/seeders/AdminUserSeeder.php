<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate([
            'email' => 'admin@woods.com',
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole('Administrator');
    }
} 