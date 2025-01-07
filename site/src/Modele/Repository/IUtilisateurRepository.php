<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

interface IUtilisateurRepository
{
    function recupererUtilisateurParEmail(string $email): ?Utilisateur;
    function recupererUtilisateursOrderedPrenomNom(): array;
    function getUtilisateursParticipantsA(int $idTableau): array;
    function getUtilisateursAffectesA(int $idCarte): array;
    public function recupererParClefPrimaire(string $valeurClePrimaire): ?AbstractDataObject;
    public function mettreAJour(AbstractDataObject $object): void;
    public function supprimer(string $valeurClePrimaire): bool;
    public function ajouter(AbstractDataObject $object): int;
}