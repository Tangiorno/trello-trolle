<?php

namespace App\Trellotrolle\Lib;

use Exception;

class MotDePasse
{
    private static string $poivre = "5QcWU25xA5XriYkC4HzgVN";

    public static function verifier(string $mdpClair, string $mdpHache): bool
    {
        return MotDePasse::hacher($mdpClair) === $mdpHache;
    }

    public static function hacher(string $mdpClair): string
    {
        return MotDePasse::$poivre . hash('sha256', $mdpClair);
    }

    /**
     * @throws Exception
     */
    public static function genererChaineAleatoire(int $nbCaracteres = 22): string
    {
        // 22 caractères par défaut pour avoir au moins 128 bits aléatoires
        $octetsAleatoires = random_bytes(ceil($nbCaracteres * 6 / 8));
        return substr(base64_encode($octetsAleatoires), 0, $nbCaracteres);
    }
}