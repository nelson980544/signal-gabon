<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Création du premier compte superviseur sur une installation neuve.
 *
 * Remplace le seeder de démonstration : en production, aucun compte ne doit
 * exister avec un mot de passe connu. Le mot de passe est saisi en interactif
 * et n'apparaît donc ni dans l'historique du shell ni dans le dépôt.
 */
class CreerSuperviseur extends Command
{
    protected $signature = 'cnlcei:creer-superviseur
                            {--name= : Nom complet de l\'agent}
                            {--email= : Adresse email de connexion}';

    protected $description = 'Créer un compte superviseur CNLCEI (installation initiale)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nom complet');
        $email = $this->option('email') ?: $this->ask('Adresse email');
        $password = $this->secret('Mot de passe');
        $confirmation = $this->secret('Confirmer le mot de passe');

        if ($password !== $confirmation) {
            $this->error('Les deux mots de passe ne correspondent pas.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            compact('name', 'email'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            ],
            [
                'name.required' => 'Le nom est obligatoire.',
                'email.required' => 'L\'adresse email est obligatoire.',
                'email.email' => 'Cette adresse email n\'est pas valide.',
                'email.unique' => 'Un compte existe déjà avec cette adresse.',
            ]
        );

        $erreurs = $validator->errors()->all();

        // Les règles de mot de passe sont vérifiées à la main : la classe
        // Password de Laravel renvoie des clés de traduction que ce projet
        // ne publie pas, et l'agent verrait « validation.password.mixed ».
        foreach ($this->faiblessesDuMotDePasse($password) as $faiblesse) {
            $erreurs[] = $faiblesse;
        }

        if ($erreurs !== []) {
            foreach ($erreurs as $erreur) {
                $this->error($erreur);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'superviseur',
            'is_active' => true,
        ]);

        $this->info("Compte superviseur créé pour {$email}.");

        return self::SUCCESS;
    }

    /**
     * @return list<string> Les raisons pour lesquelles le mot de passe est refusé.
     */
    private function faiblessesDuMotDePasse(string $password): array
    {
        $faiblesses = [];

        if (mb_strlen($password) < 12) {
            $faiblesses[] = 'Le mot de passe doit faire au moins 12 caractères.';
        }
        if (! preg_match('/\p{Ll}/u', $password)) {
            $faiblesses[] = 'Le mot de passe doit contenir une minuscule.';
        }
        if (! preg_match('/\p{Lu}/u', $password)) {
            $faiblesses[] = 'Le mot de passe doit contenir une majuscule.';
        }
        if (! preg_match('/[0-9]/', $password)) {
            $faiblesses[] = 'Le mot de passe doit contenir un chiffre.';
        }
        if (! preg_match('/[^\p{L}\p{N}]/u', $password)) {
            $faiblesses[] = 'Le mot de passe doit contenir un caractère spécial.';
        }

        return $faiblesses;
    }
}
