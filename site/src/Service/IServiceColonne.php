<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\ServiceException;

interface IServiceColonne extends IServiceGeneral
{
    /**
     * @throws ServiceException
     */
    public function getColonneById(int $idColonne): Colonne;
    
    public function recupererColonnesTableau(int $idTableau): array;

    /**
     * @param int $idColonne
     * @param string|null $idUtilisateurConnecte
     * @return void
     * @throws ServiceException
     */
    public function supprimerColonne(int $idColonne, ?string $idUtilisateurConnecte): void;
    /**
     * @throws ServiceException
     */
    public function creerColonne(int $idTableau, string $nom): Colonne;
    /**
     * @throws ServiceException
     */
    public function mettreAJour(string $titreColonne, Colonne $colonne): void;

}