<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clé de chiffrement des preuves
    |--------------------------------------------------------------------------
    |
    | Clé binaire de 32 octets encodée en base64, distincte d'APP_KEY.
    | Générer avec : php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
    | Si elle est absente, une clé dérivée d'APP_KEY est utilisée (HKDF).
    |
    */

    'files_key' => env('FILES_ENCRYPTION_KEY'),

];
