<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RegisterCommand extends Command
{
    protected $signature = 'app:register';

    protected $description = 'Register a new user and generate an API token';

    public function handle(): void
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        $this->info("$email has been registered.");
        $this->info("API token: {$token}");
    }
}
