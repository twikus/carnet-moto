<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create
                            {--name= : Nom de l\'utilisateur}
                            {--email= : Adresse email}
                            {--password= : Mot de passe}';

    protected $description = 'Créer ou réinitialiser le compte administrateur';

    public function handle(): int
    {
        $name     = $this->option('name')     ?? $this->ask('Nom');
        $email    = $this->option('email')    ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Mot de passe');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
            ]
        );

        $action = $user->wasRecentlyCreated ? 'créé' : 'mis à jour';
        $this->info("Compte {$action} : {$user->email}");

        return self::SUCCESS;
    }
}
