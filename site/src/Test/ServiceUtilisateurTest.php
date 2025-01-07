<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Lib\ConnexionUtilisateurJWT;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\IUtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceUtilisateur;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ServiceUtilisateurTest extends TestCase
{
    private $servUtilisateur;
    private $utilisateurRepositoryMock;
    private $connexionUtilisateurMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateurRepositoryMock = $this->createMock(IUtilisateurRepository::class);
        $this->connexionUtilisateurMock = $this->createMock(IConnexionUtilisateur::class);
        $this->servUtilisateur = new ServiceUtilisateur($this->utilisateurRepositoryMock, $this->connexionUtilisateurMock);
    }

    public function testFiltrerUtilisateurs()
    {
        // Préparation des données
        $uti1 = new Utilisateur('login1', 'Nom1', 'Prenom1', 'email1@yopmail.com', 'motdepasse1');
        $uti2 = new Utilisateur('login2', 'Nom2', 'Prenom2', 'email2@yopmail.com', 'motdepasse2');
        $uti3 = new Utilisateur('login3', 'Nom3', 'Prenom3', 'email3@yopmail.com', 'motdepasse3');
        $utilisateurs = [$uti1, $uti2, $uti3];
        $tableau1 = new Tableau(1, 'TitreTableau', 'Description', $uti3);
        $filtredUtilisateurs = $this->servUtilisateur->filtrerUtilisateurs($utilisateurs, $tableau1);
        $this->assertCount(2, $filtredUtilisateurs);

    }

    public function testCheckUtilisateurNonConnecte() {
        $this->connexionUtilisateurMock->method("estConnecte")->willReturn(true);
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkUtilisateurNonConnecte();
    }

    public function testModifierUtilisateur()
    {
        $utilisateur = new Utilisateur("log", 'Test', 'Utili', 'test@yopmail.com', 'mdp');
        $nomMAJ = 'nom';
        $prenomMAJ = 'prem';
        $emailMAJ = 'aze@gmail.com';
        $this->utilisateurRepositoryMock->method('mettreAJour')->willReturnCallback(function (Utilisateur $uti) use ($nomMAJ, $prenomMAJ, $emailMAJ) {
            $this->assertEquals($nomMAJ, $uti->getNom());
            $this->assertEquals($prenomMAJ, $uti->getPrenom());
            $this->assertEquals($emailMAJ, $uti->getEmail());
        });
        $this->servUtilisateur->modifierUtilisateur($utilisateur, $nomMAJ, $prenomMAJ, $emailMAJ);
    }

    public function testCheckUtilisateurConnecteEst()     {
        $this->connexionUtilisateurMock->method("getLoginUtilisateurConnecte")->willReturn("login");
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkUtilisateurConnecteEst("aze");
    }

    public function testCheckUtilisateurNonExistant()
    {
        $login = "log";
        $uti = new Utilisateur($login, "Test", "Test", "test@yopmail.com", "mdp");
        $this->utilisateurRepositoryMock->method('recupererParClefPrimaire')->with($login)->willReturn($uti);

        $this->expectException(ServiceException::class);

        $this->servUtilisateur->checkUtilisateurNonExistant($login);
    }

    public function testCheckEmptyFiltredUtilisateur()
    {
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkEmptyFiltredUtilisateur([]);
    }

    public function testCheckSamePasswords()
    {
        $mdp1 = 'motdepasse1';
        $mdp2 = 'motdepasse1';
        $mdp3 = 'aze';
        $this->servUtilisateur->checkSamePasswords($mdp1, $mdp2);
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkSamePasswords($mdp1, $mdp3);

    }

    public function testGetUserById()
    {
        $loginUtilisateur = "logTest";
        $utilisateurArg = new Utilisateur($loginUtilisateur, "Test", "Uti", "test@gmail.com", "mdp");
        $this->utilisateurRepositoryMock->method('recupererParClefPrimaire')->with($loginUtilisateur)->willReturn($utilisateurArg);
        $utilisateurFinal = $this->servUtilisateur->getUserById($loginUtilisateur);
        assertEquals($utilisateurArg, $utilisateurFinal);
    }


    public function testRecupererUtilisateursParEmail()
    {
        $email = 'test@yopmail.com';
        $utilisateur = new Utilisateur('LOG', 'N', 'Utile', $email, 'mdp');
        $this->utilisateurRepositoryMock->method('recupererUtilisateurParEmail')->with($email)->willReturn($utilisateur);
        $utiParEmail = $this->servUtilisateur->recupererUtilisateursParEmail($email);
        $this->assertEquals($utilisateur, $utiParEmail);
    }

    public function testGetUtilisateurConnecte() //Meme combat avec la connexion
    {
        $utilisateurTest = new Utilisateur("login", "nom", "prenom", "zae@yopmail.com", "mdp");
        $this->utilisateurRepositoryMock->method("recupererParClefPrimaire")->with("login")->willReturn($utilisateurTest);
        $this->connexionUtilisateurMock->method("getLoginUtilisateurConnecte")->willReturn("login");
        $utilisateurResultat = $this->servUtilisateur->getUtilisateurConnecte();
        assertEquals($utilisateurTest, $utilisateurResultat);
    }

    public function testCheckEmailValide()
    {
        $email1 = 'test@yopmail.com';
        $email2 = 'testcom';
        $this->servUtilisateur->checkEmailValide($email1);
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkEmailValide($email2);


    }

    public function testCheckMdpCorrect()
    {
        // Préparation des données
        $mdp = 'motdepasse';
        $mdpHache = MotDePasse::hacher($mdp);
        $this->servUtilisateur->checkMdpCorrect($mdp, $mdpHache);
        $this->expectException(ServiceException::class);
        $this->servUtilisateur->checkMdpCorrect($mdp, "azeae");

    }

    public function testRecupererUtilisateursOrderedPrenomNom()
    {
        $utilisateur1 = new Utilisateur('log1', 'Nom1', 'Prenom3', 'email1@yopmail.com', 'mdp');
        $utilisateur2 = new Utilisateur('log2', 'Nom2', 'Prenom2', 'email2@yopmail.com', 'mdp');
        $utilisateur3 = new Utilisateur('log3', 'Nom3', 'Prenom1', 'email3@yopmail.com', 'mdp');
        $utilisateurs = [$utilisateur3, $utilisateur2, $utilisateur1];

        $this->utilisateurRepositoryMock->method('recupererUtilisateursOrderedPrenomNom')->willReturn($utilisateurs);

        $utilisateursTries = $this->servUtilisateur->recupererUtilisateursOrderedPrenomNom();

        $this->assertCount(3, $utilisateursTries);
        $this->assertEquals('Prenom1', $utilisateursTries[0]->getPrenom());
        $this->assertEquals('Prenom2', $utilisateursTries[1]->getPrenom());
        $this->assertEquals('Prenom3', $utilisateursTries[2]->getPrenom());
    }

    public function testSupprimerUtilisateur()
    {
        $utilisateur = new Utilisateur("login", "nom", "prenom", "email@yopmail.com", "mdp");
        $this->utilisateurRepositoryMock->method("supprimer")->with("login")->willReturnCallback(function (string $login) use ($utilisateur){
            assertEquals($utilisateur->getLogin(), $login);
            return true;
        });
        $this->servUtilisateur->supprimerUtilisateur("login");
    }

    public function testCreerUtilisateur()
    {
        $login = 'testLogin';
        $nom = 'Test';
        $prenom = 'Utilisateur';
        $email = 'test@yopmail.com';
        $mdp = 'mdp';
        $this->utilisateurRepositoryMock->method('ajouter')->willReturnCallback(function (Utilisateur $utilisateur) use ($login, $nom, $prenom, $email, $mdp) {
            $this->assertEquals($login, $utilisateur->getLogin());
            $this->assertEquals($nom, $utilisateur->getNom());
            $this->assertEquals($prenom, $utilisateur->getPrenom());
            $this->assertEquals($email, $utilisateur->getEmail());
            return 1;
        });
        $this->servUtilisateur->creerUtilisateur($login, $nom, $prenom, $email, $mdp);
    }
}
