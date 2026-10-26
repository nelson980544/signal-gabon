<?php

namespace Tests\Concerns;

use App\Models\Signalement;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Données de test partagées par les suites de parcours critiques.
 */
trait FabriqueDossiers
{
    protected const MOT_DE_PASSE = 'MotDePasse!2026x';

    protected function utilisateur(string $role = 'agent', array $extra = []): User
    {
        return User::create(array_merge([
            'name' => 'Utilisateur ' . Str::random(6),
            'email' => Str::lower(Str::random(10)) . '@cnlcei.ga',
            'password' => self::MOT_DE_PASSE,
            'role' => $role,
            'is_active' => true,
        ], $extra));
    }

    protected function dossier(array $extra = []): Signalement
    {
        return Signalement::create(array_merge([
            'code' => 'SG-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4)),
            'categorie' => 'Marchés publics',
            'description' => str_repeat('Faits dénoncés avec suffisamment de détails. ', 3),
            'date_faits' => '2026-05-10',
            'province' => 'Estuaire',
            'ville' => 'Libreville',
            'statut' => 'recu',
        ], $extra));
    }

    /** Corps JSON valide pour POST /api/signalements. */
    protected function corpsSignalement(array $extra = []): array
    {
        return array_merge([
            'categorie' => 'Marchés publics',
            'description' => str_repeat('Faits dénoncés avec suffisamment de détails. ', 3),
            'date_faits' => '2026-05-10',
            'province' => 'Estuaire',
            'ville' => 'Libreville',
        ], $extra);
    }

    /** Se connecte pour de vrai (token Sanctum réel, avec date d'expiration). */
    protected function connecter(User $user): string
    {
        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => self::MOT_DE_PASSE,
        ])->assertOk()->json('token');

        $this->oublierGardes();

        return $token;
    }

    /**
     * Les tests réutilisent une seule instance d'application, alors qu'en
     * production chaque requête repart de zéro : sans cela, l'utilisateur
     * connecté par la requête précédente reste en mémoire dans les gardes et
     * le token présenté n'est plus vérifié (Sanctum retombe sur la session
     * ouverte par Auth::attempt lors de la connexion).
     */
    protected function oublierGardes(): void
    {
        $this->app['auth']->forgetGuards();
        $this->app['session.store']->flush();
    }

    protected function avecToken(string $token): static
    {
        $this->oublierGardes();

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
