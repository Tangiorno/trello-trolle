<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\U;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ICarteRepository;
use App\Trellotrolle\Modele\Repository\IColonneRepository;
use App\Trellotrolle\Modele\Repository\ITableauRepository;
use App\Trellotrolle\Modele\Repository\IUtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceColonne;
use App\Trellotrolle\Service\ServiceTableau;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

class ServiceTableauTest extends TestCase
{
    private $servTableau;
    private $carteRepositoryMock;
    private $colonneRepositoryMock;
    private $tableauRepositoryMock;
    private $utilisateurRepositoryMock;
    private $connexionUtilisateur;
    private $servColonne;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carteRepositoryMock = $this->createMock(ICarteRepository::class);
        $this->colonneRepositoryMock = $this->createMock(IColonneRepository::class);
        $this->tableauRepositoryMock = $this->createMock(ITableauRepository::class);
        $this->utilisateurRepositoryMock = $this->createMock(IUtilisateurRepository::class);
        $this->connexionUtilisateur = $this->createMock(IConnexionUtilisateur::class);
        $this->servColonne = $this->createMock(IServiceColonne::class);
        $this->servTableau = new ServiceTableau($this->carteRepositoryMock, $this->colonneRepositoryMock, $this->tableauRepositoryMock, $this->utilisateurRepositoryMock, $this->connexionUtilisateur, $this->servColonne);
    }

    public function testGetNombreTableauxTotalUtilisateur()
    {
        $uti = new Utilisateur("Log", "Nom", "Prenom", "test@yopmail.com", "mdp");
        $this->tableauRepositoryMock->method("getNombreTableauxTotalUtilisateur")->willReturn(2);
        $nb = $this->servTableau->getNombreTableauxTotalUtilisateur($uti->getLogin());
        assertEquals(2, $nb);
    }

    public function testSupprimerTableauxDeUtilisateur()
    {

    }

    public function testEstParticipant()
    {
        $login = "login";
        $proprio = new Utilisateur("aze", "zae", "eza", "zae", "zae");
        $utilisateur = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "code", "titre", $proprio, [$utilisateur]);
        assertTrue($this->servTableau->estParticipant($tab, $login));
    }

    public function testSupprimerTableau()
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $idTabSuppr = $tableau->getIdTableau();
        $colonne = new Colonne(99, "titirec1", $tableau);
        $carte = new Carte(21, "carte", "desc", "bleu", $colonne);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn($colonne);
        $this->carteRepositoryMock->method("supprimerCartesDeColonne")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($carte) {
            assertEquals($idColonne, $carte->getColonne()->getIdColonne());
        });
        $this->colonneRepositoryMock->method("supprimer")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($colonne) {
            assertEquals($idColonne, $colonne->getIdColonne());
            return true;
        });
       $this->tableauRepositoryMock->method("supprimer")->with($tableau->getIdTableau())->willReturnCallback(function (string $idTab) use ($idTabSuppr){
           assertEquals($idTab, $idTabSuppr);
           return true;
       });
        $this->servTableau->supprimerTableau($tableau);
    }

    public function testAjouterMembre()
    {
        $idTab = 1;
        $logMembre = "log";
        $proprio = new Utilisateur("aze", "zae", "eza", "zae", "zae");
        $membre = new Utilisateur($logMembre, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau($idTab, "code", "titre", $proprio);
        $this->tableauRepositoryMock->method("ajouterParticipantAtableau")->with($idTab, $logMembre)->willReturnCallback(function ($idTableau, $login) use ($tab, $membre){
            assertEquals($idTableau, $tab->getIdTableau());
            assertEquals($login, $membre->getLogin());
        });
       $this->servTableau->ajouterMembre($tab, $membre);
    }

    public function testWorksCheckAppartientTableau()
    {
        $login = "login";
        $utilisateur = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", new Utilisateur("ea", "nom", "prenom", "salut@yopmail.com", "mdp"), [$utilisateur]);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->servTableau->checkAppartientTableau($tab);
        assertEquals($login, $utilisateur->getLogin());
    }

    /**
     * @return void
     * @throws ServiceException Vous n'appartenez pas à ce tableau
     */
    public function testCrashCheckAppartientTableau()
    {
        $login = "login";
        $utilisateur = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $utilisateur);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->expectException(ServiceException::class);
        $this->servTableau->checkAppartientTableau($tab);
    }

    public function testSupprimerTableauxParticipeUtilisateur()
    {

    }

    public function testGetTableauById()
    {
        $idTab = 1;
        $tabArg = new Tableau($idTab, "azeazeaze", "Titre", new Utilisateur("Log", "Nom", "Prenom", "test@yopmail.com", "mdp"));
        $this->tableauRepositoryMock->method('recupererParClefPrimaire')->with($idTab)->willReturn($tabArg);
        $tabFinal = $this->servTableau->getTableauById($idTab);
        self::assertEquals($tabArg, $tabFinal);
    }

    public function testChangerNomTableau()
    {
        $ancienNom = "ancien";
        $newNom = "nouveau";
        $utilisateur = new Utilisateur(1, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", $ancienNom, $utilisateur);
        $tab->setTitreTableau($newNom);
        $this->tableauRepositoryMock->method("mettreAJour")->with($tab)->willReturnCallback(function (Tableau $tableau) use ($newNom, $ancienNom) {
            assertEquals($newNom, $tableau->getTitreTableau());
            assertNotEquals($ancienNom, $tableau->getTitreTableau());
        });
        $this->servTableau->changerNomTableau($tab, $newNom);

    }

    public function testGetNbColonnes()
    {
        $utilisateur = new Utilisateur("zae", "zae", "zea", "eze", "zae");
        $tab = new Tableau(1, "eza", "eza", $utilisateur);
        $this->colonneRepositoryMock->method("getNombreColonnesTotalTableau")->with($tab->getIdTableau())->willReturn(2);
        assertEquals(2, $this->servTableau->getNbColonnes($tab->getIdTableau()));
    }

    public function testEstProprietaire()
    {
        $login = "login";
        $uti = new Utilisateur($login, "nom", "prenom", "zae", "eza");
        $tab = new Tableau(1, "zea", "éze", $uti);
        assertTrue($this->servTableau->estProprietaire($tab, $login));

    }

    public function testRecupererTableauxDeUtilisateur()
    {
        $login = "login";
        $uti = new Utilisateur($login, "nom", "prenom", "zae", "eza");
        $tab = new Tableau(1, "zea", "éze", $uti);
        $this->tableauRepositoryMock->method("recupererTableauxUtilisateur")->with($login)->willReturn(array($tab));
        $array = $this->servTableau->recupererTableauxDeUtilisateur($login);
        assertNotEquals([], $array);
    }

    public function testRetirerParticipant()
    {

    }

    public function testRecupererTableauxParticipeUtilisateur()
    {
        $login = "login";
        $proprio = new Utilisateur("ea", "nom", "prenom", "zae", "eza");
        $uti = new Utilisateur($login, "nom", "prenom", "zae", "eza");
        $tab = new Tableau(1, "zea", "éze", $proprio, [$uti]);
        $this->tableauRepositoryMock->method("recupererTableauxParticipeUtilisateur")->with($login)->willReturn(array($tab));
        $array = $this->servTableau->recupererTableauxParticipeUtilisateur($login);
        assertNotEquals([], $array);
    }

    public function testCreerTableauPrerempli()
    {

    }

    public function testCheckUtilisateurCoEstParticipantOuProprietaire()
    {
        $login = "login";
        $uti = new Utilisateur($login, "nom", "prenom", "zae", "eza");
        $tab = new Tableau(1, "zea", "éze", $uti);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->servTableau->checkUtilisateurCoEstProprietaire($tab);
        assertEquals($login, $tab->getProprietaire()->getLogin());
    }

    public function testGetTableauByCode()
    {
        $codeTab = "eazeaze";
        $tabArg = new Tableau(1, $codeTab, "Titre", new Utilisateur("Log", "Nom", "Prenom", "test@yopmail.com", "mdp"));
        $this->tableauRepositoryMock->method('recupererParCodeTableau')->with($codeTab)->willReturn($tabArg);
        $tabFinal = $this->servTableau->getTableauByCode($codeTab);
        self::assertEquals($tabArg, $tabFinal);
    }

    public function testCheckUtilisateurCoEstProprietaire()
    {
        $login = "login";
        $utilisateur = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $utilisateur);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->servTableau->checkUtilisateurCoEstProprietaire($tab);
        assertEquals($login, $tab->getProprietaire()->getLogin());
    }

    public function testWorksComparerTableauxDeColonnes()
    {
        $utilisateur = new Utilisateur("login", "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $utilisateur);
        $colonne1 = new Colonne(1, "zae", $tab);
        $colonne2 = new Colonne(2, "ezae", $tab);
        $this->servTableau->comparerTableauxDeColonnes($colonne1, $colonne2);
        assertEquals($colonne1->getTableau(), $colonne2->getTableau());
    }

    /**
     * @return void
     * @throws ServiceException Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!
     */
    public function testCrashComparerTableauxDeColonnes()
    {
        $utilisateur = new Utilisateur("login", "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $utilisateur);
        $tab2 = new Tableau(3, "azeazeaze", "nom", $utilisateur);
        $colonne1 = new Colonne(1, "zae", $tab);
        $colonne2 = new Colonne(2, "ezae", $tab2);
        $this->expectException(ServiceException::class);
        $this->servTableau->comparerTableauxDeColonnes($colonne1, $colonne2);

    }

    public function testCheckParticipantOuProprietaire()
    {
        $login = "login";
        $login2 = "eaz";
        $proprio = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $participant = new Utilisateur($login2, "eaz", "aze", "azez", "ééqze");
        $tab = new Tableau(1, "azeazeaze", "nom", $proprio, [$participant]);
        $this->servTableau->checkParticipantOuProprietaire($tab, $login2);
        $this->servTableau->checkParticipantOuProprietaire($tab, $login);
        assertEquals($tab->getProprietaire()->getLogin(), $login);
    }

    public function testArrayRempliRecupererTableauxOuUtilisateurEstMembre()
    {
        $login = "login";
        $membre = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $membre);
        $this->tableauRepositoryMock->method("recupererTableauxOuUtilisateurEstMembre")->with($login)->willReturn(array($tab));
        $array = $this->servTableau->recupererTableauxOuUtilisateurEstMembre($login);
        assertNotEquals([], $array);
    }

    public function testArrayVideRecupererTableauxOuUtilisateurEstMembre()
    {
        $login = "login";
        $this->tableauRepositoryMock->method("recupererTableauxOuUtilisateurEstMembre")->with($login)->willReturn([]);
        $array = $this->servTableau->recupererTableauxOuUtilisateurEstMembre($login);
        assertEquals([], $array);
    }

    public function testWorksCheckUtilisateurCoEstPasProprietaire()
    {
        $login = "login";
        $membre = new Utilisateur("azez", "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $membre);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->servTableau->checkUtilisateurCoEstPasProprietaire($tab);
        assertNotEquals($login, $tab->getProprietaire()->getLogin());

    }

    /**
     * @return void
     * @throws ServiceException Vous ne pouvez pas quitter ce tableau
     */
    public function testCrashCheckUtilisateurCoEstPasProprietaire()
    {
        $login = "login";
        $membre = new Utilisateur($login, "nom", "prenom", "salut@yopmail.com", "mdp");
        $tab = new Tableau(1, "azeazeaze", "nom", $membre);
        $this->connexionUtilisateur->method("getLoginUtilisateurConnecte")->willReturn($login);
        $this->expectException(ServiceException::class);
        $this->servTableau->checkUtilisateurCoEstPasProprietaire($tab);
    }

    public function testCreerTableau()
    {
        $idTab = null;
        $titreTab = "titre";
        $utilisateur = new Utilisateur("login", "nom", "prenom", "zao@yopmail.com", "mdp");
        $tableau = new Tableau(null, "", "titre", $utilisateur);
        $this->utilisateurRepositoryMock->method("recupererParClefPrimaire")->with("login")->willReturn($utilisateur);
        $this->tableauRepositoryMock->method("ajouter")->with($tableau)->willReturnCallback(function (Tableau $tab) use ($idTab, $titreTab, $utilisateur) {
            assertEquals($tab->getIdTableau(), $idTab);
            assertEquals($tab->getTitreTableau(), $titreTab);
            assertEquals($tab->getProprietaire(), $utilisateur);
            return 1;
        });
        $this->servTableau->creerTableau($titreTab, $utilisateur->getLogin());
    }
}
