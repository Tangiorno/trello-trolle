<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\HTTP\Session;

class ConnexionUtilisateurJWT implements IConnexionUtilisateur
{

    public function connecter(string $loginUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["login" => $loginUtilisateur]));
    }

    public function deconnecter(): void
    {
        if (Cookie::contient("auth_token"))
            Cookie::supprimer("auth_token");
    }

    public function estUtilisateur($login): bool
    {
        return ($this->estConnecte() && $this->getLoginUtilisateurConnecte() == $login);
    }

    public function estConnecte(): bool
    {
        return !is_null($this->getLoginUtilisateurConnecte());
    }

    public function getLoginUtilisateurConnecte(): ?string
    {
        if (Cookie::contient("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["login"] ?? null;
        } else
            return null;
    }
}