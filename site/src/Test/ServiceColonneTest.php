<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ICarteRepository;
use App\Trellotrolle\Modele\Repository\IColonneRepository;
use App\Trellotrolle\Modele\Repository\ITableauRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceGeneral;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNull;

class ServiceColonneTest extends TestCase
{
    private $servColonne;
    private $colonneRepositoryMock;
    private $carteRepositoryMock;
    private $tableauRepositoryMock;
    private $connexionUtilisateurMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carteRepositoryMock = $this->createMock(ICarteRepository::class);
        $this->colonneRepositoryMock = $this->createMock(IColonneRepository::class);
        $this->tableauRepositoryMock = $this->createMock(ITableauRepository::class);
        $this->connexionUtilisateurMock = $this->createMock(IConnexionUtilisateur::class);
        $this->servColonne = new ServiceColonne($this->colonneRepositoryMock, $this->carteRepositoryMock, $this->tableauRepositoryMock, $this->connexionUtilisateurMock);
    }

    public function testRecupererColonnesTableau()
    {
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $listColonnes = [new Colonne(99, "titirec1", $tableau), new Colonne(88, "titirec2", $tableau)];
        $this->colonneRepositoryMock->method("recupererColonnesTableau")->willReturn($listColonnes);
        $colonnes = $this->servColonne->recupererColonnesTableau(1);
        assertCount(2, $colonnes);

    }

    public function testWorksGetColonneById()
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne1 = new Colonne($idColonne, "titirec1", $tableau);
        $colonne2 = new Colonne(21, "titrezaa", $tableau);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn($colonne1);
        $colonneRep = $this->servColonne->getColonneById($idColonne);
        assertEquals($colonneRep, $colonne1);
        assertNotEquals($colonneRep, $colonne2);
    }

     public function testCrashGetColonneById()
    {
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne1 = new Colonne(3, "titirec1", $tableau);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with(3)->willReturn(null);
        $this->expectException(ServiceException::class);
        $colonnefail = $this->servColonne->getColonneById(3);
    }

    public function testWorkSupprimerColonne()
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne = new Colonne(99, "titirec1", $tableau);
        $carte = new Carte(21, "carte", "desc", "bleu", $colonne);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn($colonne);
        $this->carteRepositoryMock->method("supprimerCartesDeColonne")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($carte){
            assertEquals($idColonne, $carte->getColonne()->getIdColonne());
        });
        $this->colonneRepositoryMock->method("supprimer")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($colonne){
            assertEquals($idColonne, $colonne->getIdColonne());
            return true;
        });
        $this->servColonne->supprimerColonne($colonne->getIdColonne(), $utilisateur->getLogin());

    }

    /**
     * @return void
     * @throws ServiceException Il faut être connecté pour supprimer une colonne
     */
    public function testCrash1WorkSupprimerColonne()//Connexion crash
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne = new Colonne(99, "titirec1", $tableau);
        $carte = new Carte(21, "carte", "desc", "bleu", $colonne);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn($colonne);
        $this->carteRepositoryMock->method("supprimerCartesDeColonne")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($carte){
            assertEquals($idColonne, $carte->getColonne()->getIdColonne());
        });
        $this->colonneRepositoryMock->method("supprimer")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($colonne){
            assertEquals($idColonne, $colonne->getIdColonne());
            return true;
        });
        $this->expectException(ServiceException::class);
        $this->servColonne->supprimerColonne($colonne->getIdColonne(), null);

    }

    /**
     * @return void
     * @throws ServiceException Colonne inconnue
     */
    public function testCrash2SupprimerColonne() //colonne null
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne = new Colonne(99, "titirec1", $tableau);
        $carte = new Carte(21, "carte", "desc", "bleu", $colonne);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->servColonne->supprimerColonne($colonne->getIdColonne(), $utilisateur->getLogin());
    }

    /**
     * @return void
     * @throws ServiceException Seuls l'auteur et les membres du tableau peuvent supprimer une colonne
     */
    public function testCrash3SupprimerColonne() //colonne null
    {
        $idColonne = 99;
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne = new Colonne(99, "titirec1", $tableau);
        $carte = new Carte(21, "carte", "desc", "bleu", $colonne);
        $this->colonneRepositoryMock->method("recupererParClefPrimaire")->with($idColonne)->willReturn($colonne);
        $this->carteRepositoryMock->method("supprimerCartesDeColonne")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($carte){
            assertEquals($idColonne, $carte->getColonne()->getIdColonne());
        });
        $this->colonneRepositoryMock->method("supprimer")->with($idColonne)->willReturnCallback(function (string $idColonne) use ($colonne){
            assertEquals($idColonne, $colonne->getIdColonne());
            return true;
        });
        $this->expectException(ServiceException::class);
        $this->servColonne->supprimerColonne($colonne->getIdColonne(), "crash");
    }

    public function testMettreAJour()
    {
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $colonne = new Colonne(99, "titirec1", $tableau);
        $titreMAJ = "colonne";
        $this->colonneRepositoryMock->method("mettreAJour")->willReturnCallback(function (Colonne $colonne) use ($titreMAJ) {
            assertEquals($colonne->getTitreColonne(), $titreMAJ);
            return $colonne->getTitreColonne() == $titreMAJ;
        });
        $this->servColonne->mettreAJour($titreMAJ, $colonne);

    }

    public function testCreerColonne()
    {
        $utilisateur = new Utilisateur("bonjour", "salut", "yp", "aze@gmail.com", "hache");
        $tableau = new Tableau(1, "aezaeaze", "titre", $utilisateur);
        $nomColonne = 'colonne';
        $this->connexionUtilisateurMock->method("estConnecte")->willReturn(true);
        $this->tableauRepositoryMock->method("recupererParClefPrimaire")->with(1)->willReturn($tableau);
        $this->colonneRepositoryMock->method('ajouter')->willReturnCallback(function (Colonne $colonne) use ($nomColonne, $tableau) {
            $this->assertEquals($nomColonne, $colonne->getTitreColonne());
            $this->assertEquals($tableau, $colonne->getTableau());
            return 1;
        });
        $this->servColonne->creerColonne($tableau->getIdTableau(), $nomColonne);
    }

}
