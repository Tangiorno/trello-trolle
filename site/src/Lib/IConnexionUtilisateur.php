<?php

namespace App\Trellotrolle\Lib;

interface IConnexionUtilisateur
{
    public function connecter(string $loginUtilisateur): void;

    public function deconnecter(): void;

    public function estUtilisateur($login): bool;

    public function estConnecte(): bool;

    public function getLoginUtilisateurConnecte(): ?string;

}