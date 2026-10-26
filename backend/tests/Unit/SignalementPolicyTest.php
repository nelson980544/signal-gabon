<?php

namespace Tests\Unit;

use App\Models\Signalement;
use App\Models\User;
use App\Policies\SignalementPolicy;
use PHPUnit\Framework\TestCase;

/**
 * La politique ne dépend que des attributs des modèles : aucune base requise.
 */
class SignalementPolicyTest extends TestCase
{
    private function user(int $id, string $role): User
    {
        $u = new User(['role' => $role]);
        $u->id = $id;

        return $u;
    }

    private function dossier(?int $agentId): Signalement
    {
        $s = new Signalement();
        $s->agent_id = $agentId;

        return $s;
    }

    public function test_le_superviseur_voit_et_modifie_tous_les_dossiers(): void
    {
        $politique = new SignalementPolicy();
        $superviseur = $this->user(1, 'superviseur');

        foreach ([null, 1, 2] as $agentId) {
            $this->assertTrue($politique->view($superviseur, $this->dossier($agentId)));
            $this->assertTrue($politique->update($superviseur, $this->dossier($agentId)));
        }
    }

    public function test_l_agent_gere_uniquement_ses_dossiers(): void
    {
        $politique = new SignalementPolicy();
        $agent = $this->user(2, 'agent');

        $this->assertTrue($politique->view($agent, $this->dossier(2)));
        $this->assertTrue($politique->update($agent, $this->dossier(2)));
    }

    public function test_l_agent_n_accede_pas_au_dossier_d_un_collegue(): void
    {
        $politique = new SignalementPolicy();
        $agent = $this->user(2, 'agent');

        $this->assertFalse($politique->view($agent, $this->dossier(3)));
        $this->assertFalse($politique->update($agent, $this->dossier(3)));
    }

    public function test_l_agent_n_accede_pas_a_un_dossier_non_attribue(): void
    {
        $politique = new SignalementPolicy();
        $agent = $this->user(2, 'agent');

        $this->assertFalse($politique->view($agent, $this->dossier(null)));
        $this->assertFalse($politique->update($agent, $this->dossier(null)));
    }

    public function test_un_role_inconnu_n_a_aucun_privilege(): void
    {
        $politique = new SignalementPolicy();
        $intrus = $this->user(9, 'admin');

        $this->assertFalse($politique->view($intrus, $this->dossier(2)));
        $this->assertFalse($politique->update($intrus, $this->dossier(null)));
    }
}
