<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FabriqueDossiers;
use Tests\TestCase;

/**
 * Parcours 2 : les routes /api/admin* et /api/auth* exigent un token.
 */
class AccesAdminTest extends TestCase
{
    use FabriqueDossiers;
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: string}> */
    public static function routesProtegees(): array
    {
        return [
            'liste des dossiers' => ['GET', '/api/admin/signalements'],
            'détail d\'un dossier' => ['GET', '/api/admin/signalements/1'],
            'mise à jour d\'un dossier' => ['PATCH', '/api/admin/signalements/1'],
            'consultation d\'une preuve' => ['GET', '/api/admin/signalements/1/preuves/1'],
            'statistiques admin' => ['GET', '/api/admin/stats'],
            'liste des agents' => ['GET', '/api/admin/agents'],
            'profil' => ['GET', '/api/auth/me'],
            'déconnexion' => ['POST', '/api/auth/logout'],
            'renouvellement' => ['POST', '/api/auth/refresh'],
        ];
    }

    #[DataProvider('routesProtegees')]
    public function test_les_routes_protegees_refusent_l_acces_sans_token(string $methode, string $url): void
    {
        $this->dossier();

        $this->json($methode, $url)->assertUnauthorized();
    }

    #[DataProvider('routesProtegees')]
    public function test_les_routes_protegees_refusent_un_token_invalide(string $methode, string $url): void
    {
        $this->withHeader('Authorization', 'Bearer 1|nimportequoi')->json($methode, $url)->assertUnauthorized();
    }

    #[DataProvider('routesProtegees')]
    public function test_les_routes_protegees_repondent_401_et_non_500_sans_en_tete_json(string $methode, string $url): void
    {
        // Un navigateur ou curl n'envoie pas toujours Accept: application/json.
        $reponse = $this->call($methode, $url);

        $this->assertSame(401, $reponse->getStatusCode(), 'Réponse inattendue : ' . $reponse->getStatusCode());
    }

    public function test_les_routes_publiques_restent_accessibles_sans_token(): void
    {
        $dossier = $this->dossier();

        $this->getJson('/api/stats/publiques')->assertOk();
        $this->getJson("/api/signalements/{$dossier->code}/statut")->assertOk();
    }
}
