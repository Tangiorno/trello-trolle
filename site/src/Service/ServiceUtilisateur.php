<?php namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\U;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\IUtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use PDOException;

class ServiceUtilisateur extends ServiceGeneral implements IServiceUtilisateur
{
    public function __construct(private readonly IUtilisateurRepository $utilisateurRepository, private readonly IConnexionUtilisateur $connexionUtilisateur)
    {
        parent::__construct($this->connexionUtilisateur);
    }

    public function getUserById(string $login): Utilisateur
    {
        $user = $this->utilisateurRepository->recupererParClefPrimaire($login);

        if (!$user) throw new ServiceException("L'utilisateur $login n'existe pas !");

        return $user;
    }

    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
    }

    public function creerUtilisateur(string $login, string $nom, string $prenom, string $email, string $mdp): Utilisateur
    {
        $mdpHache = MotDePasse::hacher($mdp);

        $u = new Utilisateur($login, $nom, $prenom, $email, $mdpHache);

        try {
            $this->utilisateurRepository->ajouter($u);
        } catch (PDOException) {
            throw new ServiceException("Une erreur est survenue lors de la création de l'utilisateur.");
        }

        return $u;
    }

    public function supprimerUtilisateur(string $login): void
    {
        $this->utilisateurRepository->supprimer($login);
    }

    public function modifierUtilisateur(Utilisateur $utilisateur, string $nom, string $prenom, string $email, ?string $mdpHache = null): void
    {
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setEmail($email);
        if ($mdpHache) $utilisateur->setMdpHache($mdpHache);
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    public function recupererUtilisateursParEmail(string $email): Utilisateur
    {
        $users = $this->utilisateurRepository->recupererUtilisateurParEmail($email);
        if (!$users) throw new ServiceException("Aucun compte n'est associé à cette adresse mail");
        return $users;
    }

    public function checkUtilisateurNonConnecte(): void
    {
        if ($this->connexionUtilisateur->estConnecte()) throw new ServiceException("Vous ne pouvez pas créer de compte ou vous connecter quand vous êtes connecté.");
    }

    public function checkSamePasswords(string $mdp, string $mdp2): void
    {
        if ($mdp !== $mdp2) throw new ServiceException("Mots de passe distincts.");
    }

    public function checkEmailValide(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ServiceException("Email non valide");
    }

    public function checkUtilisateurNonExistant(string $login): void
    {
        try {
            $this->getUserById($login);
        }
        catch (ServiceException) {
            return;
        }
        throw new ServiceException("Le login est déjà pris.");
    }

    public function checkUtilisateurConnecteEst(string $login): void
    {
        if ($login != $this->connexionUtilisateur->getLoginUtilisateurConnecte())
            throw new ServiceException("Le compte ne vous apparient pas. Cette tentative sera reportée.");
    }

    /**
     * @throws ServiceException
     */
    public function checkMdpCorrect(string $mdp, string $mdpHache): void
    {
        if (!(MotDePasse::verifier($mdp, $mdpHache))) {
            throw new ServiceException('Mot de passe erroné', 401);
        }
    }

    function getUtilisateurConnecte(): Utilisateur
    {
        return $this->getUserById($this->connexionUtilisateur->getLoginUtilisateurConnecte());
    }

    function filtrerUtilisateurs(array $utilisateurs, Tableau $tableau): array
    {
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {
            return !$tableau->estParticipantOuProprietaire($u->getLogin());
        });
        return $filtredUtilisateurs;
    }

    function checkEmptyFiltredUtilisateur(array $filtredUtilisateurs) : void {
        if (empty($filtredUtilisateurs)) {
            throw new ServiceException("Il n'est pas possible d'ajouter plus de membres à ce tableau.");
        }

    }
}