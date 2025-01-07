<?php

namespace App\Trellotrolle\Configuration;

class ConfigurationSite
{
    static public function getDureeExpirationSession(): int
    {
        return 36000;
    }
//https://webinfo.iutmontp.univ-montp2.fr/~tordeuxm/trellotrolle-code-de-base/web/utilisateur/recuperation
    static public function url(): string{
        return "https://webinfo.iutmontp.univ-montp2.fr/~tordeuxm/trellotrolle-code-de-base/";
    }
}