<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

interface ITableauRepository
{
    function recupererTableauxUtilisateur(string $login): array;
    function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;
    function recupererTableauxOuUtilisateurEstMembre(string $login): array;
    function recupererTableauxParticipeUtilisateur(string $login): array;
    function getNombreTableauxTotalUtilisateur(string $login): int;
    function ajouterParticipantAtableau(int $idTableau, string $login): void;
    function retirerParticipant(int $getIdTableau, string $login): void;
    public function recupererParClefPrimaire(string $valeurClePrimaire): ?AbstractDataObject;
    public function mettreAJour(AbstractDataObject $object): void;
    public function supprimer(string $valeurClePrimaire): bool;
    public function ajouter(AbstractDataObject $object): int;

}
