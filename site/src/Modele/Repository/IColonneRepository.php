<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;

interface IColonneRepository
{
    /**
     * @param int $idTableau
     * @return Colonne[]
     */
    public function recupererColonnesTableau(int $idTableau): array;
    public function getNombreColonnesTotalTableau(int $idTableau): int;
    public function recupererParClefPrimaire(string $valeurClePrimaire): ?AbstractDataObject;
    public function mettreAJour(AbstractDataObject $object): void;
    public function supprimer(string $valeurClePrimaire): bool;
    public function ajouter(AbstractDataObject $object): int;
}