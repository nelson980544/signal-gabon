<?php

namespace Database\Seeders;

use App\Models\Signalement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SignalementSeeder extends Seeder
{
    private function generateCode(): string
    {
        do {
            $part1 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
            $part2 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
            $code = "SG-{$part1}-{$part2}";
        } while (Signalement::where('code', $code)->exists());
        return $code;
    }

    public function run(): void
    {
        $agents = User::where('role', 'agent')->get();

        $data = [
            ['categorie' => 'Administration publique', 'description' => 'Un fonctionnaire du ministère de l\'intérieur demande systématiquement des pots-de-vin pour délivrer des documents administratifs courants comme les cartes d\'identité et passeports.', 'date_faits' => Carbon::now()->subDays(5)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Libreville', 'statut' => 'traite', 'agent_id' => $agents[0]->id, 'message_agent' => 'Dossier traité et transmis aux autorités compétentes.'],
            ['categorie' => 'Police et gendarmerie', 'description' => 'Des agents de police sur le rond-point du Stade Omar Bongo réclament de l\'argent aux conducteurs en échange de ne pas rédiger de procès-verbaux pour des infractions mineures ou inexistantes.', 'date_faits' => Carbon::now()->subDays(10)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Libreville', 'statut' => 'en_instruction', 'agent_id' => $agents[1]->id, 'message_agent' => 'Enquête en cours avec les services internes.'],
            ['categorie' => 'Santé et hôpitaux', 'description' => 'Au Centre Hospitalier Universitaire de Libreville, des infirmiers exigent des paiements informels pour accélérer l\'accès aux soins d\'urgence et aux médicaments normalement gratuits pour les patients démunis.', 'date_faits' => Carbon::now()->subDays(15)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Libreville', 'statut' => 'attribue', 'agent_id' => $agents[2]->id, 'message_agent' => null],
            ['categorie' => 'Éducation et universités', 'description' => 'Un enseignant de l\'Université Omar Bongo propose des notes de passage en échange de paiements en espèces ou de faveurs. Plusieurs étudiants ont été victimes de cette pratique répétée depuis plusieurs semestres.', 'date_faits' => Carbon::now()->subDays(20)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Libreville', 'statut' => 'en_examen', 'agent_id' => null, 'message_agent' => null],
            ['categorie' => 'Douanes et frontières', 'description' => 'Des agents des douanes au port d\'Owendo laissent passer des conteneurs non déclarés en échange de rémunérations versées directement aux agents sans reçu officiel ni traçabilité.', 'date_faits' => Carbon::now()->subDays(25)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Owendo', 'statut' => 'recu', 'agent_id' => null, 'message_agent' => null],
            ['categorie' => 'Justice et tribunaux', 'description' => 'Un greffier du Tribunal de Première Instance de Libreville demande des sommes d\'argent pour accélérer le traitement des dossiers judiciaires et les inscriptions au rôle des audiences.', 'date_faits' => Carbon::now()->subDays(30)->toDateString(), 'province' => 'Estuaire', 'ville' => 'Libreville', 'statut' => 'traite', 'agent_id' => $agents[3]->id, 'message_agent' => 'Signalement transmis à l\'Inspection Générale des Services Judiciaires.'],
            ['categorie' => 'Marchés publics', 'description' => 'Attribution frauduleuse d\'un marché public de construction de route dans la province du Haut-Ogooué à une entreprise non qualifiée ayant versé des commissions occultes à des fonctionnaires.', 'date_faits' => Carbon::now()->subDays(35)->toDateString(), 'province' => 'Haut-Ogooué', 'ville' => 'Franceville', 'statut' => 'en_instruction', 'agent_id' => $agents[4]->id, 'message_agent' => 'Vérification des documents de marché en cours.'],
            ['categorie' => 'Administration publique', 'description' => 'La mairie de Port-Gentil exige des paiements non officiels pour l\'obtention de permis de construire, même pour des constructions parfaitement conformes aux normes en vigueur.', 'date_faits' => Carbon::now()->subDays(40)->toDateString(), 'province' => 'Ogooué-Maritime', 'ville' => 'Port-Gentil', 'statut' => 'attribue', 'agent_id' => $agents[0]->id, 'message_agent' => null],
            ['categorie' => 'Santé et hôpitaux', 'description' => 'À l\'hôpital provincial de Mouila, des médicaments destinés aux patients démunis sont détournés et revendus dans des pharmacies privées par des employés de l\'établissement hospitalier.', 'date_faits' => Carbon::now()->subDays(45)->toDateString(), 'province' => 'Ngounié', 'ville' => 'Mouila', 'statut' => 'en_examen', 'agent_id' => null, 'message_agent' => null],
            ['categorie' => 'Police et gendarmerie', 'description' => 'Des gendarmes à la sortie de Tchibanga rançonnent régulièrement les transporteurs ruraux en confisquant leurs documents puis en exigeant de l\'argent pour les récupérer.', 'date_faits' => Carbon::now()->subDays(50)->toDateString(), 'province' => 'Nyanga', 'ville' => 'Tchibanga', 'statut' => 'recu', 'agent_id' => null, 'message_agent' => null],
            ['categorie' => 'Éducation et universités', 'description' => 'Dans plusieurs lycées de la province du Woleu-Ntem, des directeurs détournent les frais de scolarité collectés auprès des familles qui ne sont jamais versés au trésor public de l\'État.', 'date_faits' => Carbon::now()->subDays(55)->toDateString(), 'province' => 'Woleu-Ntem', 'ville' => 'Oyem', 'statut' => 'classe', 'agent_id' => $agents[1]->id, 'message_agent' => 'Dossier classé suite à absence de preuves suffisantes pour engager des poursuites.'],
            ['categorie' => 'Marchés publics', 'description' => 'Un projet d\'électrification rurale dans l\'Ogooué-Ivindo financé par l\'État a été accordé à une société fictive dont les responsables sont proches d\'un haut fonctionnaire du ministère.', 'date_faits' => Carbon::now()->subDays(60)->toDateString(), 'province' => 'Ogooué-Ivindo', 'ville' => 'Makokou', 'statut' => 'en_instruction', 'agent_id' => $agents[2]->id, 'message_agent' => null],
            ['categorie' => 'Douanes et frontières', 'description' => 'Des agents des eaux et forêts facilitent l\'exportation illégale de bois précieux à Lambaréné en échange de paiements illicites de la part d\'exploitants forestiers opérant sans autorisation.', 'date_faits' => Carbon::now()->subDays(70)->toDateString(), 'province' => 'Moyen-Ogooué', 'ville' => 'Lambaréné', 'statut' => 'traite', 'agent_id' => $agents[3]->id, 'message_agent' => 'Rapport transmis au procureur de la République de Lambaréné.'],
            ['categorie' => 'Administration publique', 'description' => 'Des agents du cadastre à Koulamoutou monnayent la délivrance de titres fonciers sur des terrains appartenant à l\'État, créant des droits de propriété frauduleux au profit de particuliers.', 'date_faits' => Carbon::now()->subDays(80)->toDateString(), 'province' => 'Ogooué-Lolo', 'ville' => 'Koulamoutou', 'statut' => 'en_examen', 'agent_id' => null, 'message_agent' => null],
            ['categorie' => 'Autre', 'description' => 'Un directeur régional d\'un organisme public impose à ses subordonnés des cotisations mensuelles obligatoires non officielles sous peine de sanctions professionnelles et de mutations disciplinaires.', 'date_faits' => Carbon::now()->subDays(90)->toDateString(), 'province' => 'Haut-Ogooué', 'ville' => 'Moanda', 'statut' => 'recu', 'agent_id' => null, 'message_agent' => null],
        ];

        foreach ($data as $item) {
            $item['code'] = $this->generateCode();
            Signalement::create($item);
        }
    }
}
