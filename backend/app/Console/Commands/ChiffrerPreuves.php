<?php

namespace App\Console\Commands;

use App\Models\Preuve;
use App\Services\FileCrypto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ChiffrerPreuves extends Command
{
    protected $signature = 'preuves:chiffrer';

    protected $description = 'Chiffre les preuves stockées en clair (idempotent).';

    public function handle(): int
    {
        $fait = 0;
        $manquants = 0;

        Preuve::where('chiffre', false)->orderBy('id')->each(function (Preuve $p) use (&$fait, &$manquants) {
            if (! Storage::exists($p->chemin)) {
                $this->warn("Fichier introuvable pour la preuve #{$p->id} ({$p->chemin})");
                $manquants++;

                return;
            }
            // Reprise après interruption : fichier déjà chiffré mais drapeau non posé.
            if (! FileCrypto::isEncrypted($p->chemin)) {
                FileCrypto::encryptStoredInPlace($p->chemin);
            }
            $p->forceFill(['chiffre' => true])->save();
            $fait++;
        });

        $this->info("{$fait} preuve(s) chiffrée(s), {$manquants} introuvable(s).");

        return $manquants > 0 ? self::FAILURE : self::SUCCESS;
    }
}
