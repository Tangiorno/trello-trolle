<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

interface ICarteRepository
{
    function recupererCartesColonne(int $idcolonne): array;
    function recupererCartesTableau(int $idTableau): array;
    function recupererCartesUtilisateur(string $login): array;
    function getNombreCartesTotalUtilisateur(string $login): int;
    function supprimerCartesDeColonne(string $idcolonne): void;
    function supprimerCartesDeEtreAffecte(string $idcarte): void;
    function setAffectations(int $idCarte, array $affectations): void;
    public function recupererParClefPrimaire(string $valeurClePrimaire): ?AbstractDataObject;
    public function mettreAJour(AbstractDataObject $object): void;
    public function supprimer(string $valeurClePrimaire): bool;
}