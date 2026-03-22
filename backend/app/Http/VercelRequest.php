<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * Request Vercel : sur les cold starts, php://input n'est lisible qu'une fois.
 * On le capture dans api/index.php ($GLOBALS['_vercel_raw_input']) et on
 * réinjecte ici si getContent() retourne vide.
 */
class VercelRequest extends Request
{
    public function getContent(bool $asResource = false): mixed
    {
        $content = parent::getContent($asResource);

        if (!$asResource && empty($content) && !empty($GLOBALS['_vercel_raw_input'])) {
            $this->content = $GLOBALS['_vercel_raw_input'];
            // Réinitialiser le JSON ParameterBag pour qu'il soit recalculé
            $this->json = null;
            return $this->content;
        }

        return $content;
    }
}
