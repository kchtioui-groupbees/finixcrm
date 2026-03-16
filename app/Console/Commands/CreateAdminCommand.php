<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email} {--role=owner} {--name=Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user (owner or staff)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->option('role');
        $name = $this->option('name');

        if (!in_array($role, [User::ROLE_OWNER, User::ROLE_STAFF])) {
            $this->error('Invalid role. Use owner or staff.');
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists.');
            return;
        }

        $password = $this->secret('Enter password for the new admin');
        $confirmPassword = $this->secret('Confirm password');

        if ($password !== $confirmPassword) {
            $this->error('Passwords do not match.');
            return;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->info("Admin user {$email} created successfully as {$role}.");
    }
}
