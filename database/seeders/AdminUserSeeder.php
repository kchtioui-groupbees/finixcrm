<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kchtioui@finix.tn'],
            [
                'name' => 'FinixTN Admin',
                'password' => Hash::make('Finix@Tn@2025'),
                'role' => User::ROLE_OWNER,
                'email_verified_at' => now(),
            ]
        );
    }
}
