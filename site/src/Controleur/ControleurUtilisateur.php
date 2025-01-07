<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\VerificationEmail;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceCarte;
use App\Trellotrolle\Service\IServiceTableau;
use App\Trellotrolle\Service\IServiceUtilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public function __construct(private readonly ContainerInterface    $container,
                                private readonly IServiceCarte         $serviceCarte,
                                private readonly IServiceTableau       $serviceTableau,
                                private readonly IServiceUtilisateur   $serviceUtilisateur,
                                private readonly IConnexionUtilisateur $connexionUtilisateurSession,
                                private readonly IConnexionUtilisateur $connexionUtilisateurJWT)
    {
        parent::__construct($this->container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route(path: '/utilisateur/{login}/details', name: 'afficherDetail', methods: ['GET'])]
    public function afficherDetail(): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();

            $utilisateur = $this->serviceUtilisateur->getUtilisateurConnecte();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }

        return self::afficherVue('utilisateur/detail', [
            "utilisateur" => $utilisateur
        ]);
    }

    #[Route(path: '/utilisateurs/inscription', name: 'afficherFormulaireCreation', methods: ['GET'])]
    public function afficherFormulaireCreation(): Response
    {
        try {
            $this->serviceUtilisateur->checkUtilisateurNonConnecte();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }
        return self::afficherVue('utilisateur/formulaireCreation');
    }

    #[Route(path: '/utilisateurs/inscription', name: 'creerDepuisFormulaire', methods: ['POST'])]
    public function creerDepuisFormulaire(): Response
    {
        try {
            $this->serviceUtilisateur->checkUtilisateurNonConnecte();

            $this->serviceUtilisateur->checkIssetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"]);

            $this->serviceUtilisateur->checkSamePasswords($_POST["mdp"], $_POST["mdp2"]);

            $this->serviceUtilisateur->checkEmailValide($_POST["email"]);

            $this->serviceUtilisateur->checkUtilisateurNonExistant($_POST["login"]);

            $utilisateur = $this->serviceUtilisateur->creerUtilisateur($_POST["login"], $_POST["nom"], $_POST["prenom"], $_POST["email"], $_POST["mdp"]);

            $this->serviceTableau->creerTableauPrerempli("Mon tableau", $utilisateur->getLogin());

            Cookie::enregistrer("login", $_POST["login"]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("afficherFormulaireCreation");
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return self::rediriger("afficherFormulaireConnexion");
    }

    #[Route(path: '/utilisateur/{login}/supprimer', name: 'supprimer', methods: ['GET'])]
    public function supprimer($login): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();

            $this->serviceUtilisateur->checkUtilisateurConnecteEst($login);

            $this->serviceTableau->supprimerTableauxDeUtilisateur($login);

            $this->serviceCarte->supprimerCartesUtilisateur($login);

            $this->serviceTableau->supprimerTableauxParticipeUtilisateur($login);

            $this->serviceUtilisateur->supprimerUtilisateur($login);

            Cookie::supprimer("login");

            $this->connexionUtilisateurSession->deconnecter();
            //$this->connexionUtilisateurJWT->deconnecter();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }

        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return self::rediriger("afficherFormulaireConnexion");
    }

    #[Route(path: '/utilisateur/{login}/miseajour', name: 'afficherFormulaireMiseAJour', methods: ['GET'])]
    public function afficherFormulaireMiseAJour(): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();

            $utilisateur = $this->serviceUtilisateur->getUtilisateurConnecte();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }

        return self::afficherVue('utilisateur/formulaireMiseAJour', [
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/{login}/miseajour', name: 'mettreAJour', methods: ['POST'])]
    public function mettreAJour($login): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();

            $this->serviceUtilisateur->checkIssetAndNotNull(["prenom", "nom", "email", "mdpActuel"]);

            $utilisateur = $this->serviceUtilisateur->getUserById($login);

            $this->serviceUtilisateur->checkEmailValide($_POST["email"]);

            $this->serviceUtilisateur->checkMdpCorrect($_POST["mdpActuel"], $utilisateur->getMdpHache());

            if (isset($_POST["new_mdp"], $_POST["new_mdp2"])) {
                $mdp = $_POST["new_mdp"];
                $mdp2 = $_POST["new_mdp2"];
                $this->serviceUtilisateur->checkSamePasswords($mdp, $mdp2);
                $this->serviceUtilisateur->modifierUtilisateur($utilisateur, $_POST["nom"], $_POST["prenom"], $_POST["email"], MotDePasse::hacher($mdp));
            } else {
                $this->serviceUtilisateur->modifierUtilisateur($utilisateur, $_POST["nom"], $_POST["prenom"], $_POST["email"]);
            }

            $cartes = $this->serviceCarte->recupererCartesUtilisateur($login);
            foreach ($cartes as $carte) {
                $participants = $carte->getAffectationsCarte();
                $participants = array_filter($participants, function ($u) use ($login) {
                    return $u->getLogin() !== $login;
                });
                $participants[] = $utilisateur;
                $this->serviceCarte->modifierAffectations($carte, $participants);
            }
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("afficherFormulaireMiseAJour");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
        return self::rediriger("afficherListeMesTableaux");
    }

    #[Route(path: '/utilisateurs/deconnexion', name: 'deconnecter', methods: ['GET'])]
    public function deconnecter(): Response
    {
        try {
            $this->serviceUtilisateur->checkConnexion();
            $this->connexionUtilisateurSession->deconnecter();
            $this->connexionUtilisateurJWT->deconnecter();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }

        MessageFlash::ajouter("success", "Vous avez été déconnecté avec succès.");
        return self::rediriger("accueil");
    }

    #[Route(path: '/utilisateurs/connexion', name: 'afficherFormulaireConnexion', methods: ['GET'])]
    public function afficherFormulaireConnexion(): Response
    {
        try {
            $this->serviceUtilisateur->checkUtilisateurNonConnecte();
        } catch (ServiceException $e) {
            MessageFlash::ajouter('warning', $e->getMessage());
            return self::rediriger('afficherListeMesTableaux');
        }
        return self::afficherVue('utilisateur/formulaireConnexion', [
            "cookieLogin" => Cookie::contient("login") ? Cookie::lire("login") : "",
        ]);
    }

    #[Route(path: '/utilisateurs/connexion', name: 'connecter', methods: ['POST'])]
    public function connecter(): Response
    {
        if ($this->connexionUtilisateurSession->estConnecte() || $this->connexionUtilisateurJWT->estConnecte()) {
            return self::rediriger("afficherListeMesTableaux");
        }
        try {
            $this->serviceUtilisateur->checkIssetAndNotNull(['login', 'mdp']);
            $utilisateur = $this->serviceUtilisateur->getUserById($_POST["login"]);
            $this->serviceUtilisateur->checkMdpCorrect($_POST['mdp'], $utilisateur->getMdpHache());

            $this->connexionUtilisateurSession->connecter($utilisateur->getLogin());
            $this->connexionUtilisateurJWT->connecter($utilisateur->getLogin());
            Cookie::enregistrer("login", $_POST["login"]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::rediriger("accueil");
        }
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return self::rediriger("afficherListeMesTableaux");
    }

    #[Route(path: '/utilisateurs/recuperation', name: 'afficherFormulaireRecuperationCompte', methods: ['GET'])]
    public function afficherFormulaireRecuperationCompte(): Response
    {
        try {
            $this->serviceUtilisateur->checkUtilisateurNonConnecte();
        } catch (ServiceException $e) {
            MessageFlash::ajouter('warning', $e->getMessage());
            return self::rediriger('afficherListeMesTableaux');
        }
        return self::afficherVue('utilisateur/resetCompte');
    }

    #[Route(path: '/utilisateurs/recuperation', name: 'recupererCompte', methods: ['POST'])]
    public function mailRecuperationCompte(): Response
    {
        try {
            $this->serviceUtilisateur->checkIssetAndNotNull(['email']);
            $utilisateurs = $this->serviceUtilisateur->recupererUtilisateursParEmail($_POST["email"]);
            VerificationEmail::envoyerMailMdpOublie($utilisateurs);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::rediriger('accueil');
        }
        return self::afficherVue('base/accueil');
    }

    #[Route(path: '/utilisateur/resetMDP/{login}', name: 'afficherResetMDP', methods: ['GET'])]
    public function afficherFormulaireResetMDP($login): Response
    {
        return self::afficherVue('utilisateur/resultatResetCompte', ["login" => $login]);
    }

    #[Route(path: '/utilisateur/resetMDP/{login}', name: 'resetMDP', methods: ['POST'])]
    public function resetMDP($login): Response
    {
        try {
            $this->serviceUtilisateur->checkSamePasswords($_POST['mdp'], $_POST['mdp2']);
            $utilisateur = $this->serviceUtilisateur->getUserById($login);
            $this->serviceUtilisateur->modifierUtilisateur($utilisateur, $utilisateur->getNom(), $utilisateur->getPrenom(), $utilisateur->getEmail(), MotDePasse::hacher($_POST['mdp']));
        } catch (ServiceException $e) {
            MessageFlash::ajouter('warning', $e->getMessage());
            self::rediriger('afficherResetMDP');
        }
        MessageFlash::ajouter('success', "Mot de passe modifié");
        return self::rediriger('afficherFormulaireConnexion');
    }
}