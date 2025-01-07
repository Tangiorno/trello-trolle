<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject
{
    public function __construct(
        private string $login,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $mdpHache,
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau): Utilisateur
    {
        return new Utilisateur(
            $objetFormatTableau["login"],
            $objetFormatTableau["nom"],
            $objetFormatTableau["prenom"],
            $objetFormatTableau["email"],
            $objetFormatTableau["mdphache"],
        );
    }
    
    public function formatTableau(): array
    {
        return array(
            "loginTag" => $this->login,
            "nomTag" => $this->nom,
            "prenomTag" => $this->prenom,
            "emailTag" => $this->email,
            "mdphacheTag" => $this->mdpHache,
        );
    }


    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     */
    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMdpHache(): string
    {
        return $this->mdpHache;
    }

    /**
     * @param string $mdpHache
     */
    public function setMdpHache(string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    public function jsonSerialize(): array
    {
        return [
            "login" => $this->getLogin(),
            "nom" => $this->getNom(),
            "prenom" => $this->getPrenom(),
            "email" => $this->getEmail(),
            "mdphache" => $this->getMdpHache(),
        ];
    }
}