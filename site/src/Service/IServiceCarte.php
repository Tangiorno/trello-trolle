<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;

interface IServiceCarte extends IServiceGeneral
{
    /**
     * @throws ServiceException
     */
    function getCarte(int $idCarte): Carte;
    /**
     * @param int $idCarte
     * @param string|null $idUtilisateurConnecte
     * @return void
     * @throws ServiceException
     */
    function supprimerCarte(int $idCarte, ?string $idUtilisateurConnecte): void;
    /**
     * @throws ServiceException
     */
    function recupererCartesTableau(Tableau $tableau): array;
    /**
     * @throws ServiceException
     */
    function creerCarte(int $idColonne, string $titre, string $descriptif, string $couleur, array $affectations): Carte;
    /**
     * @param Carte $carte
     * @param Colonne $colonne
     * @param string $titre
     * @param string $descriptif
     * @param string $couleur
     * @param Utilisateur[] $affectations
     * @return void
     * @throws ServiceException
     */
    function mettreAJour(Carte $carte, Colonne $colonne, string $titre, string $descriptif, string $couleur, array $affectations): void;
    /**
     * @throws ServiceException
     */
    function recupererCartesColonne(int $idColonne): array;
    /**
     * @throws ServiceException
     */
    function modifierAffectations(Carte $carte, array $affectations): void;
    /**
     * @throws ServiceException
     */
    function recupererCartesUtilisateur(string $login): array;
    /**
     * @param string $login
     * @return void
     * @throws ServiceException
     */
    function supprimerCartesUtilisateur(string $login): void;
    /**
     * @param array $cartes
     * @param Utilisateur $utilisateur
     * @return void
     * @throws ServiceException
     */
    function modifierAffectationsCartes(array $cartes, Utilisateur $utilisateur): void;
    /**
     * @param string $login
     * @param Tableau $tableau
     * @return int[]
     * @throws ServiceException
     */
    function enleverUtilisateurDeAffectationsDeTableau(string $login, Tableau $tableau): array;
}