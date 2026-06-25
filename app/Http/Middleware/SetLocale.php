<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set the application locale from the authenticated user's preference.
     * Falls back to the app default locale if not authenticated.
     */
    public function handle(Request $oRequest, Closure $fnNext): Response
    {
        $sLocale = config('app.locale', 'fr');

        if ($oRequest->user()) {
            $sUserLocale = $oRequest->user()->locale ?? $sLocale;
            if (in_array($sUserLocale, array_keys(config('app.supported_locales', ['fr' => 'fr', 'en' => 'en'])), true)) {
                $sLocale = $sUserLocale;
            }
        }

        App::setLocale($sLocale);

        return $fnNext($oRequest);
    }
}
