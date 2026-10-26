<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Durée de conservation des dossiers clos
    |--------------------------------------------------------------------------
    |
    | Exprimée en mois, elle s'applique aux dossiers « traité » et « classé ».
    | Elle est annoncée au citoyen dans la politique de confidentialité : les
    | deux doivent rester cohérentes. 0 désactive la purge.
    |
    */

    'retention_dossiers_mois' => (int) env('RETENTION_DOSSIERS_MOIS', 0),

];
