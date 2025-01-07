<?php namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ICarteRepository;
use App\Trellotrolle\Modele\Repository\IColonneRepository;
use App\Trellotrolle\Modele\Repository\IUtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class ServiceCarte extends ServiceGeneral implements IServiceCarte
{
    public function __construct(
        private readonly ICarteRepository       $carteRepository,
        private readonly IColonneRepository     $colonneRepository,
        private readonly IUtilisateurRepository $utilisateurRepository,
        private readonly IConnexionUtilisateur  $connexionUtilisateur
    )
    {
        parent::__construct($this->connexionUtilisateur);
    }

    public function getCarte(int $idCarte): Carte
    {
        $carte = $this->carteRepository->recupererParClefPrimaire($idCarte);

        if (!$carte) throw new ServiceException("Carte inexistante.");

        return $carte;
    }

    /**
     * @throws ServiceException
     */
    public function supprimerCarte(int $idCarte, ?string $idUtilisateurConnecte): void
    {
        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClefPrimaire($idCarte);

        if (is_null($idUtilisateurConnecte))
            throw new ServiceException("Il faut être connecté pour supprimer une carte", Response::HTTP_UNAUTHORIZED);

        if ($carte === null)
            throw new ServiceException("Carte inconnue.", Response::HTTP_NOT_FOUND);

        if (!$carte->getColonne()->getTableau()->estParticipantOuProprietaire($idUtilisateurConnecte))
            throw new ServiceException("Seuls l'auteur et les membres du tableau peuvent supprimer une carte", Response::HTTP_FORBIDDEN);

        $this->carteRepository->supprimer($idCarte);
    }

    /**
     * @param Tableau $tableau
     * @return Carte[]
     */
    public function recupererCartesTableau(Tableau $tableau): array
    {
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }

    /**
     * @param int $idColonne
     * @param string $titre
     * @param string $descriptif
     * @param string $couleur
     * @param array $affectations
     * @return Carte
     * @throws ServiceException
     */
    public function creerCarte(int $idColonne, string $titre, string $descriptif, string $couleur, array $affectations): Carte
    {
        $colonne = $this->colonneRepository->recupererParClefPrimaire($idColonne);
        $this->checkIssetAndNotNullForObjectsArray([$colonne]);
        if (strlen($titre) > 250) throw new ServiceException("Le titre ne peut pas dépasser 50 caractères!", Response::HTTP_BAD_REQUEST);

        if ($affectations && !$affectations[0] instanceof Utilisateur)
            $affectations = array_map(function (string $login) {
                return $this->utilisateurRepository->recupererParClefPrimaire($login);
            }, $affectations);

        $carte = new Carte(null, $titre, $descriptif, $couleur, $colonne, $affectations);

        $idCarte = $this->carteRepository->ajouter($carte);
        $carte->setIdCarte($idCarte);

        if ($affectations)
            $this->carteRepository->setAffectations($carte->getIdCarte(), array_map(function (Utilisateur $utilisateur) {
                return $utilisateur->getLogin();
            }, $affectations));

        return $carte;
    }

    public function mettreAJour(Carte $carte, Colonne $colonne, string $titre, string $descriptif, string $couleur, array $affectations): void
    {
        $carte->setColonne($colonne);
        $carte->setTitreCarte($titre);
        $carte->setDescriptifCarte($descriptif);
        $carte->setCouleurCarte($couleur);
        $this->carteRepository->mettreAJour($carte);

        $this->carteRepository->setAffectations($carte->getIdCarte(), array_map(function (Utilisateur $utilisateur) {
            return $utilisateur->getLogin();
        }, $affectations));
    }

    public function recupererCartesColonne(int $idColonne): array
    {
        return $this->carteRepository->recupererCartesColonne($idColonne);
    }

    public function modifierAffectations(Carte $carte, array $affectations): void
    {
        if ($affectations)
            $this->carteRepository->setAffectations($carte->getIdCarte(), array_map(function (Utilisateur $utilisateur) {
                return $utilisateur->getLogin();
            }, $affectations));
    }

    public function recupererCartesUtilisateur(string $login): array
    {
        return $this->carteRepository->recupererCartesUtilisateur($login);
    }

    public function supprimerCartesUtilisateur(string $login): void
    {
        $cartes = $this->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $this->modifierAffectations($carte, $participants);
        }
    }

    public function modifierAffectationsCartes(array $cartes, Utilisateur $utilisateur): void
    {
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $this->modifierAffectations($carte, $affectations);
        }
    }

    public function enleverUtilisateurDeAffectationsDeTableau(string $login, Tableau $tableau): array
    {
        $cartes = $this->recupererCartesTableau($tableau);
        $ids = [];
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($login) {
                return $u->getLogin() != $login;
            });
            if ($affectations != $carte->getAffectationsCarte()) {
                $this->modifierAffectations($carte, $affectations);
                $ids[] = $carte->getIdCarte();
            }
        }
        return $ids;
    }
}