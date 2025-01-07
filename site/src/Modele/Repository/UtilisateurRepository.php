<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Lib\U;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

class UtilisateurRepository extends AbstractRepository implements IUtilisateurRepository
{
    public function __construct(
        private readonly IConnexionBaseDeDonnees $connexionBaseDeDonnees
    )
    {
        parent::__construct($this->connexionBaseDeDonnees);
    }


    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }

    public function recupererUtilisateurParEmail(string $email): ?Utilisateur
    {
        $sql = "Select * from utilisateur where email=:Tag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["Tag"=>$email]);
        $obj = $pdoStatement->fetch();
        return $this->construireDepuisTableau($obj);
    }
    /**
     * @param int $idTableau
     * @return Utilisateur[] les utilisateurs qui participent au tableau
     */
    public function getUtilisateursParticipantsA(int $idTableau): array
    {
        $sql = "Select u.login, nom, prenom, email, mdphache 
                FROM utilisateur u 
                JOIN participer p ON u.login = p.login 
                WHERE idtableau = :Tag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("Tag" => $idTableau);
        $pdoStatement->execute($values);
        $listeUti = array();
        foreach ($pdoStatement as $item) {
            $listeUti[] = Utilisateur::construireDepuisTableau($item);
        }
        return $listeUti;
    }
    
    /**
     * @param int $idCarte
     * @return Utilisateur[] les utilisateurs affectés à la carte
     */
    public function getUtilisateursAffectesA(int $idCarte): array
    {
        $sql = "Select u.login, nom, prenom, email, mdphache 
                FROM utilisateur u 
                JOIN etreaffecte p ON u.login = p.login  
                WHERE idcarte = :Tag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("Tag" => $idCarte);
        $pdoStatement->execute($values);
        $listeUti = array();
        foreach ($pdoStatement as $item) {
            $listeUti[] = Utilisateur::construireDepuisTableau($item);
        }
        return $listeUti;
    }
    
    protected function getNomTable(): string
    {
        return "utilisateur";
    }

    protected function getNomCle(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }
}