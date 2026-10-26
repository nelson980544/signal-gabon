<?php

namespace App\Console\Commands;

use App\Models\Signalement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Efface les dossiers clos au-delà de la durée de conservation.
 *
 * La loi impose d'annoncer une durée de conservation et de s'y tenir. La durée
 * elle-même est une décision de la CNLCEI, pas une décision technique : elle se
 * règle par RETENTION_DOSSIERS_MOIS, et la commande ne supprime rien tant que
 * --confirmer n'est pas passé.
 */
class PurgerDossiers extends Command
{
    protected $signature = 'cnlcei:purger-dossiers
                            {--mois= : Durée de conservation en mois (défaut : RETENTION_DOSSIERS_MOIS)}
                            {--confirmer : Supprimer réellement ; sans cette option, rien n\'est effacé}';

    protected $description = 'Effacer les dossiers clos dont la durée de conservation est dépassée';

    public function handle(): int
    {
        $mois = (int) ($this->option('mois') ?: config('signalgabon.retention_dossiers_mois'));

        if ($mois < 1) {
            $this->error('Durée de conservation non définie. Renseignez RETENTION_DOSSIERS_MOIS dans le .env.');

            return self::FAILURE;
        }

        $limite = now()->subMonths($mois);

        // Seuls les dossiers clos sont concernés : une instruction en cours ne
        // doit jamais disparaître, quelle que soit son ancienneté.
        $dossiers = Signalement::with('preuves')
            ->whereIn('statut', ['traite', 'classe'])
            ->where('updated_at', '<', $limite)
            ->get();

        if ($dossiers->isEmpty()) {
            $this->info("Aucun dossier clos depuis plus de {$mois} mois.");

            return self::SUCCESS;
        }

        $preuves = $dossiers->sum(fn ($d) => $d->preuves->count());

        $this->line("Dossiers clos avant le {$limite->format('d/m/Y')} : {$dossiers->count()}");
        $this->line("Preuves associées : {$preuves}");

        if (! $this->option('confirmer')) {
            $this->newLine();
            $this->warn('Simulation : rien n\'a été supprimé.');
            $this->line('Relancez avec --confirmer pour effacer définitivement.');

            return self::SUCCESS;
        }

        foreach ($dossiers as $dossier) {
            foreach ($dossier->preuves as $preuve) {
                Storage::delete($preuve->chemin);
            }
            // Les preuves et l'historique partent en cascade (contraintes de
            // clé étrangère), le dossier referme la boucle.
            $dossier->delete();
        }

        // Le journal garde la trace de la purge, jamais son contenu.
        Log::info('Purge des dossiers clos', [
            'duree_conservation_mois' => $mois,
            'dossiers_supprimes' => $dossiers->count(),
            'preuves_supprimees' => $preuves,
        ]);

        $this->info("{$dossiers->count()} dossier(s) et {$preuves} preuve(s) supprimés définitivement.");

        return self::SUCCESS;
    }
}
