<?php namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\ICarteRepository;
use App\Trellotrolle\Modele\Repository\IColonneRepository;
use App\Trellotrolle\Modele\Repository\ITableauRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class ServiceColonne extends ServiceGeneral implements IServiceColonne
{
    public function __construct(
        private readonly IColonneRepository $colonneRepository,
        private readonly ICarteRepository   $carteRepository,
        private readonly ITableauRepository $tableauRepository,
        private readonly IConnexionUtilisateur $connexionUtilisateur
    )
    {
        parent::__construct($this->connexionUtilisateur);
    }

    public function getColonneById(int $idColonne): Colonne
    {
        $col = $this->colonneRepository->recupererParClefPrimaire($idColonne);
        if (!$col) {
            throw new ServiceException("Colonne inexistante.");
        }
        return $col;
    }

    public function recupererColonnesTableau(int $idTableau): array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }

    public function supprimerColonne(int $idColonne, ?string $idUtilisateurConnecte): void
    {
        $colonne = $this->colonneRepository->recupererParClefPrimaire($idColonne);

        if (is_null($idUtilisateurConnecte))
            throw new ServiceException("Il faut être connecté pour supprimer une colonne", Response::HTTP_UNAUTHORIZED);

        if ($colonne === null)
            throw new ServiceException("Colonne inconnue.", Response::HTTP_NOT_FOUND);

        if (!$colonne->getTableau()->estParticipantOuProprietaire($idUtilisateurConnecte))
            throw new ServiceException("Seuls l'auteur et les membres du tableau peuvent supprimer une colonne", Response::HTTP_FORBIDDEN);

        foreach ($this->carteRepository->recupererCartesColonne($idColonne) as $carte) {
            $this->carteRepository->supprimerCartesDeEtreAffecte($carte->getIdCarte());
        }
        $this->carteRepository->supprimerCartesDeColonne($idColonne);
        $this->colonneRepository->supprimer($idColonne);
    }

    public function creerColonne(int $idTableau, string $nom): Colonne
    {
        $this->checkConnexion();
        $tableau = $this->tableauRepository->recupererParClefPrimaire($idTableau);
        $this->checkIssetAndNotNullForObjectsArray([$tableau]);
        if (strlen($nom) > 250) throw new ServiceException("Le titre ne peut pas dépasser 50 caractères!", Response::HTTP_BAD_REQUEST);

        $colonne = new Colonne(null, $nom, $tableau);
        $idColonne = $this->colonneRepository->ajouter($colonne);
        $colonne->setIdColonne($idColonne);

        return $colonne;
    }
    public function mettreAJour(string $titreColonne, Colonne $colonne): void
    {
        $colonne->setTitreColonne($titreColonne);
        $this->colonneRepository->mettreAJour($colonne);
    }
}