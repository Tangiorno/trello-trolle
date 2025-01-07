<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;

interface IServiceUtilisateur extends IServiceGeneral
{
    /**
     * @throws ServiceException
     */
    function getUserById(string $login): Utilisateur;
    /**
     * @throws ServiceException
     */
    function recupererUtilisateursOrderedPrenomNom(): array;
    /**
     * @throws ServiceException
     */
    function creerUtilisateur(string $login, string $nom, string $prenom, string $email, string $mdp): Utilisateur;
    /**
     * @throws ServiceException
     */
    function supprimerUtilisateur(string $login): void;
    /**
     * @throws ServiceException
     */
    function modifierUtilisateur(Utilisateur $utilisateur, string $nom, string $prenom, string $email, ?string $mdpHache = null): void;
    /**
     * @throws ServiceException
     */
    function recupererUtilisateursParEmail(string $email): Utilisateur;
    /**
     * @throws ServiceException
     */
    function checkUtilisateurNonConnecte(): void;
    /**
     * @param string $mdp
     * @param string $mdp2
     * @return void
     * @throws ServiceException
     */
    function checkSamePasswords(string $mdp, string $mdp2): void;
    /**
     * @param string $email
     * @return void
     * @throws ServiceException
     */
    function checkEmailValide(string $email): void;
    /**
     * @param string $login
     * @return void
     * @throws ServiceException
     */
    function checkUtilisateurNonExistant(string $login): void;
    /**
     * @throws ServiceException
     */
    function checkUtilisateurConnecteEst(string $login): void;

    public function checkMdpCorrect(string $mdp, string $mdpHache): void;

    /**
     * @return Utilisateur
     * @throws ServiceException
     */
    function getUtilisateurConnecte(): Utilisateur;

    /**
     * @param Utilisateur[] $utilisateurs
     * @param Tableau $tableau
     * @return Utilisateur[]
     */
     function filtrerUtilisateurs(array $utilisateurs,Tableau $tableau): array;

    /**
     * @param Utilisateur[] $filtredUtilisateurs
     * @return void
     * @throws ServiceException
     */
     function checkEmptyFiltredUtilisateur(array $filtredUtilisateurs) : void;

}