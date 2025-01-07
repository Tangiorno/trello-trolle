<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceCarte;
use App\Trellotrolle\Service\IServiceColonne;
use App\Trellotrolle\Service\IServiceTableau;
use App\Trellotrolle\Service\IServiceUtilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableau extends ControleurGenerique
{

    public function __construct(private readonly ContainerInterface    $container,
                                private readonly IServiceCarte         $serviceCarte,
                                private readonly IServiceColonne       $serviceColonne,
                                private readonly IServiceTableau       $serviceTableau,
                                private readonly IServiceUtilisateur   $serviceUtilisateur,
                                private readonly IConnexionUtilisateur $connexionUtilisateur
    )
    {
        parent::__construct($this->container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route(path: '/tableau/{codeTableau}', name: 'afficherTableau', methods: ['GET'])]
    public function afficherTableau($codeTableau): Response
    {
        try {
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
            $colonnes = $this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau());
            $cartesMatrix = [];
            $participants = [];
            foreach ($colonnes as $colonne) {
                $cartes = $this->serviceCarte->recupererCartesColonne($colonne->getIdColonne());
                foreach ($cartes as $carte) {
                    foreach ($carte->getAffectationsCarte() as $utilisateur) {
                        if (!isset($participants[$utilisateur->getLogin()])) {
                            $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                        }
                        if (!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                            $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                        }
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                    }
                }
                $cartesMatrix[] = $cartes;
            }
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::afficherVue('accueil');
        }
        return self::afficherVue('tableau/tableau', [
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "cartesMatrix" => $cartesMatrix
        ]);
    }

    #[Route(path: '/tableau/{codeTableau}/miseajour', name: 'afficherFormulaireMiseAJourTableau', methods: ['GET'])]
    public function afficherFormulaireMiseAJourTableau($codeTableau): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
            $this->serviceTableau->checkParticipantOuProprietaire($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return self::rediriger('accueil');
        }
        return self::afficherVue('tableau/formulaireMiseAJourTableau', [
            "codeTableau" => $codeTableau,
            "nomTableau" => $tableau->getTitreTableau()
        ]);
    }

    #[Route(path: '/tableaux/creation', name: 'afficherFormulaireCreationTableau', methods: ['GET'])]
    public function afficherFormulaireCreationTableau(): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::afficherVue('afficherFormulaireConnexion');
        }
        return self::afficherVue('tableau/formulaireCreationTableau');
    }

    #[Route(path: '/tableaux/creation', name: 'creerTableau', methods: ['POST'])]
    public function creerTableau(): Response
    {
        try {
            $this->serviceTableau->checkConnexionEtAreSet(["nomTableau"]);
            $tableau = $this->serviceTableau->creerTableau($_POST["nomTableau"], $this->connexionUtilisateur->getLoginUtilisateurConnecte());
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::rediriger('accueil');
        }
        return self::rediriger("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{codeTableau}/miseajour', name: 'mettreAJourTableau', methods: ['POST'])]
    public function mettreAJourTableau($codeTableau): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
            $this->serviceTableau->checkIssetAndNotNull(["nomTableau"]);
            $this->serviceTableau->checkParticipantOuProprietaire($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceTableau->changerNomTableau($tableau, $_POST["nomTableau"]);

        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::rediriger('accueil');
        }
        return self::rediriger("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
    
    #[Route(path: '/tableaux/liste', name: 'afficherListeMesTableaux', methods: ['GET'])]
    public function afficherListeMesTableaux(): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
            $tableaux = $this->serviceTableau->recupererTableauxOuUtilisateurEstMembre($login);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::rediriger('accueil');
        }
        return self::afficherVue('tableau/listeTableauxUtilisateur', [
            "tableaux" => $tableaux,
        ]);
    }

    #[Route(path: '/tableau/{codeTableau}/quitter', name: 'quitterTableau', methods: ['GET'])]
    public function quitterTableau($codeTableau): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
            $utilisateur = $this->serviceUtilisateur->getUtilisateurConnecte();
            $this->serviceTableau->checkUtilisateurCoEstPasProprietaire($tableau);
            $this->serviceTableau->checkAppartientTableau($tableau);
            $this->serviceTableau->retirerParticipant($tableau, $utilisateur->getLogin());
            $cartes = $this->serviceCarte->recupererCartesTableau($tableau);
            $this->serviceCarte->modifierAffectationsCartes($cartes, $utilisateur);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::rediriger('accueil');
        }
        return self::rediriger("afficherListeMesTableaux");
    }

    #[Route(path: '/tableau/{codeTableau}/supprimer', name: 'supprimerTableau', methods: ['GET'])]
    public function supprimerTableau($codeTableau): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
            $this->serviceTableau->checkUtilisateurCoEstProprietaire($tableau);
            $this->serviceTableau->supprimerTableau($tableau);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter('danger', $exception->getMessage());
            return self::rediriger('accueil');
        }
        return self::rediriger("afficherListeMesTableaux");
    }
}
