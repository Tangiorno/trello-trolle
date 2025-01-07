<?php namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ICarteRepository;
use App\Trellotrolle\Modele\Repository\IColonneRepository;
use App\Trellotrolle\Modele\Repository\ITableauRepository;
use App\Trellotrolle\Modele\Repository\IUtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use PDOException;

class ServiceTableau extends ServiceGeneral implements IServiceTableau
{
    public function __construct(
        private readonly ICarteRepository       $carteRepository,
        private readonly IColonneRepository     $colonneRepository,
        private readonly ITableauRepository     $tableauRepository,
        private readonly IUtilisateurRepository $utilisateurRepository,
        private readonly IConnexionUtilisateur $connexionUtilisateur,
        private readonly IServiceColonne        $serviceColonne,
    )
    {
        parent::__construct($this->connexionUtilisateur);
    }

    /**
     * @param int $idTableau
     * @return AbstractDataObject|null
     * @throws ServiceException
     */
    public function getTableauById(int $idTableau): ?AbstractDataObject
    {
        $tableau = $this->tableauRepository->recupererParClefPrimaire($idTableau);
        if (!$tableau) throw new ServiceException("Tableau inexistant");
        return $tableau;
    }

    public function getNbColonnes(int $idTableau): int
    {
        return $this->colonneRepository->getNombreColonnesTotalTableau($idTableau);
    }

    public function getTableauByCode(string $codeTableau): Tableau
    {
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        return $tableau;
    }

    /**
     * @param string $nomTableau
     * @param string $username
     * @return Tableau
     * @throws PDOException
     */
    public function creerTableau(string $nomTableau, string $username): Tableau
    {
        $tableau = new Tableau(
            null,
            "",
            $nomTableau,
            $this->utilisateurRepository->recupererParClefPrimaire($username)
        );

        $idtab = $this->tableauRepository->ajouter($tableau);
        $codeTableau = hash("sha256", $username . $idtab);

        $tableau->setIdTableau($idtab);
        $tableau->setCodeTableau($codeTableau);

        $this->tableauRepository->mettreAJour($tableau);

        return $tableau;
    }

    /**
     * @param string $nomTableau
     * @param string $username
     * @return Tableau
     * @throws ServiceException
     */
    public function creerTableauPrerempli(string $nomTableau, string $username): Tableau
    {
        $tableau = $this->creerTableau($nomTableau, $username);

        $nomsColonnes = ["TODO", "DOING", "DONE"];

        for ($i = 0; $i < 3; $i++) {
            $colonne = new Colonne(
                null,
                $nomsColonnes[$i],
                $tableau,
            );
            try {
                $this->colonneRepository->ajouter($colonne);
            } catch (PDOException) {
                throw new ServiceException("Une erreur est survenue lors de la création d'une des colonnes de démo'.");
            }

            try {
                $this->carteRepository->ajouter(new Carte(
                    null,
                    "Exemple",
                    "Exemple de carte",
                    "#FFFFFF",
                    $colonne
                ));
            } catch (PDOException) {
                throw new ServiceException("Une erreur est survenue lors de la création d'une des cartes de démo'.");
            }
        }

        return $tableau;
    }

    public function changerNomTableau(Tableau $tableau, string $nouveauNomTableau): void
    {
        $tableau->setTitreTableau($nouveauNomTableau);
        $this->tableauRepository->mettreAJour($tableau);
    }

    public function retirerParticipant(Tableau $tableau, string $login): void
    {
        if ($tableau->getProprietaire() == $login)
            throw new ServiceException("Vous ne pouvez pas vous supprimer du tableau.");
        
        if (!$tableau->estParticipantOuProprietaire($login))
            throw new ServiceException("Cet utilisateur n'est pas membre du tableau");
        
        $this->tableauRepository->retirerParticipant($tableau->getIdTableau(), $login);
    }

    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        return $this->tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);

    }

    public function getNombreTableauxTotalUtilisateur(string $login): int
    {
        return $this->tableauRepository->getNombreTableauxTotalUtilisateur($login);
    }

    public function supprimerTableau(Tableau $tableau): void
    {
        foreach ($this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau()) as $colonne) {
            $this->serviceColonne->supprimerColonne($colonne->getIdColonne(), $this->connexionUtilisateur->getLoginUtilisateurConnecte());
        }
        $this->tableauRepository->supprimer($tableau->getIdTableau());
    }

    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        return $this->tableauRepository->recupererTableauxParticipeUtilisateur($login);
    }

    public function recupererTableauxDeUtilisateur(string $login): array
    {
        return $this->tableauRepository->recupererTableauxUtilisateur($login);
    }

    public function ajouterMembre(Tableau $tableau, Utilisateur $utilisateur): void
    {
        $this->tableauRepository->ajouterParticipantAtableau($tableau->getIdTableau(), $utilisateur->getLogin());
    }

    public function checkUtilisateurCoEstParticipantOuProprietaire(Tableau $tab): void
    {
        $this->checkParticipantOuProprietaire($tab, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
    }

    public function checkParticipantOuProprietaire(Tableau $tab, string $login): void
    {
        if (!$tab->estParticipantOuProprietaire($login)) {
            throw new ServiceException("Vous n'avez pas de droits d'éditions sur ce tableau");
        }
    }

    public function estProprietaire(Tableau $tab, string $login): bool
    {
        return $tab->getProprietaire()->getLogin() === $login;
    }

    public function estParticipant(Tableau $tab, string $login): bool
    {
        foreach ($tab->getParticipants() as $participant) {
            if ($participant->getLogin() === $login) {
                return true;
            }
        }
        return false;
    }

    public function checkAppartientTableau(Tableau $tableau) : void {
        if (!$this->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'appartenez pas à ce tableau");
        }
    }

    public function comparerTableauxDeColonnes(Colonne $c1, Colonne $c2): void
    {
        if ($c1->getTableau()->getIdTableau() !== $c2->getTableau()->getIdTableau()) {
            throw new ServiceException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
        }
    }


    public function supprimerTableauxDeUtilisateur(string $login): void
    {
        $tabs = $this->recupererTableauxDeUtilisateur($login);
        foreach ($tabs as $tab) {
            $this->supprimerTableau($tab);
        }
    }

    public function supprimerTableauxParticipeUtilisateur(string $login): void
    {
        $tableaux = $this->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $this->retirerParticipant($tableau, $login);
        }
    }
    public function checkUtilisateurCoEstProprietaire(Tableau $tableau) : void{
        if (!$this->estProprietaire($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }
    }

    public function checkUtilisateurCoEstPasProprietaire(Tableau $tableau) : void{
        if ($this->estProprietaire($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous ne pouvez pas quitter ce tableau");
        }
    }
}
