<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Session;

class ConnexionUtilisateurSession implements IConnexionUtilisateur
{
    private string $cleConnexion = "_utilisateurConnecte";

    public function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $loginUtilisateur);
    }

    public function deconnecter(): void
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    public function estUtilisateur($login): bool
    {
        return ($this->estConnecte() &&
            $this->getLoginUtilisateurConnecte() == $login
        );
    }

    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient($this->cleConnexion);
    }

    public function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }
}
