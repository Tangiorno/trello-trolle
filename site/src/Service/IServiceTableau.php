<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;

interface IServiceTableau extends IServiceGeneral
{
    /**
     * @throws ServiceException
     */
    function getTableauById(int $idTableau): ?AbstractDataObject;
    /**
     * @throws ServiceException
     */
    function getNbColonnes(int $idTableau): int;
    /**
     * @throws ServiceException
     */
    function getTableauByCode(string $codeTableau): Tableau;
    /**
     * @throws ServiceException
     */
    function creerTableau(string $nomTableau, string $username): Tableau;
    /**
     * @throws ServiceException
     */
    function creerTableauPrerempli(string $nomTableau, string $username): Tableau;
    /**
     * @throws ServiceException
     */
    function changerNomTableau(Tableau $tableau, string $nouveauNomTableau): void;
    /**
     * @throws ServiceException
     */
    function recupererTableauxOuUtilisateurEstMembre(string $login): array;
    /**
     * @throws ServiceException
     */
    function getNombreTableauxTotalUtilisateur(string $login): int;
    /**
     * @throws ServiceException
     */
    function supprimerTableau(Tableau $tableau): void;
    /**
     * @throws ServiceException
     */
    function recupererTableauxParticipeUtilisateur(string $login): array;
    /**
     * @throws ServiceException
     */
    function recupererTableauxDeUtilisateur(string $login): array;
    /**
     * @throws ServiceException
     */
    /**
     * @throws ServiceException
     */
    function retirerParticipant(Tableau $tableau, string $login): void;
    /**
     * @throws ServiceException
     */
    function ajouterMembre(Tableau $tableau, Utilisateur $utilisateur): void;
    /**
     * @throws ServiceException
     */
    function checkUtilisateurCoEstParticipantOuProprietaire(Tableau $tab): void;
    /**
     * @throws ServiceException
     */
    function checkParticipantOuProprietaire(Tableau $tab, string $login): void;
    /**
     * @throws ServiceException
     */
    function estProprietaire(Tableau $tab, string $login): bool;

    /**
     * @param Tableau $tab
     * @param string $login
     * @return bool
     */
    function estParticipant(Tableau $tab, string $login): bool;
    /**
     * @param Colonne $c1
     * @param Colonne $c2
     * @return void
     * @throws ServiceException
     */
    function comparerTableauxDeColonnes(Colonne $c1, Colonne $c2): void;
    /**
     * @param string $login
     * @return void
     * @throws ServiceException
     */
    function supprimerTableauxDeUtilisateur(string $login): void;
    /**
     * @param string $login
     * @return void
     * @throws ServiceException
     */

    /**
     * @param Tableau $tableau
     * @return void
     * @throws ServiceException
     */
    public function checkAppartientTableau(Tableau $tableau) : void;

    /**
     * @param string $login
     * @return void
     */
    function supprimerTableauxParticipeUtilisateur(string $login): void;

    /**
     * @param Tableau $tableau
     * @return void
     * @throws ServiceException
     */
    public function checkUtilisateurCoEstPasProprietaire(Tableau $tableau) : void;

    /**
     * @param Tableau $tableau
     * @return void
     * @throws ServiceException
     */
    public function checkUtilisateurCoEstProprietaire(Tableau $tableau) : void;

}
