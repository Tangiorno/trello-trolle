<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;

class CarteRepository extends AbstractRepository implements ICarteRepository
{
    public function __construct(
        private readonly IConnexionBaseDeDonnees $connexionBaseDeDonnees,
        private readonly IColonneRepository      $colonneRepository,
        private readonly IUtilisateurRepository  $utilisateurRepository,
    )
    {
        parent::__construct($this->connexionBaseDeDonnees);
    }

    public function recupererCartesColonne(int $idcolonne): array
    {
        $sql = "SELECT * FROM carte WHERE idcolonne = $idcolonne";
        $prep = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $objs = [];
        foreach ($prep as $cart) {
            $objs[] = $this->construireDepuisTableau($cart);
        }
        return $objs;
    }

    public function recupererCartesTableau(int $idTableau): array
    {
        $sql = "SELECT *
                FROM carte ca
                    JOIN colonne co ON ca.idcolonne = co.idcolonne 
                WHERE idtableau = $idTableau";
        $prep = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $objs = [];
        foreach ($prep as $cart) {
            $objs[] = $this->construireDepuisTableau($cart);
        }
        return $objs;
    }

    public function supprimerCartesDeColonne(string $idcolonne): void
    {
        $sql = "
            DELETE
            FROM carte
            WHERE idcolonne = $idcolonne;
        ";
        $this->connexionBaseDeDonnees->getPDO()->query($sql);
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        self::supprimerCartesDeEtreAffecte($valeurClePrimaire);
        return parent::supprimer($valeurClePrimaire);
    }

    public function supprimerCartesDeEtreAffecte(string $idcarte): void
    {
        $sql = "
            DELETE
            FROM etreaffecte
            WHERE idCarte = $idcarte;
        ";
        $this->connexionBaseDeDonnees->getPDO()->query($sql);
    }

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "select {$this->formatNomsColonnes()}
                from carte
                where idcarte in (
                    select idcarte
                    from etreaffecte
                    where login = :loginTag
                )
        ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);
        $pdoStatement->execute([
            'loginTag' => $login
        ]);
        $cartes = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $cartes[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $cartes;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $objetFormatTableau["colonne"] = $this->colonneRepository->recupererParClefPrimaire($objetFormatTableau["idcolonne"]);
        $objetFormatTableau["affectationsCarte"] = $this->utilisateurRepository->getUtilisateursAffectesA($objetFormatTableau["idcarte"]);
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function getNombreCartesTotalUtilisateur(string $login): int
    {
        $sql = "SELECT COUNT(idcarte) as count FROM etreaffecte WHERE login = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj['count'];
    }

    /**
     * @param int $idCarte
     * @param string[] $affectations
     * @return void
     */
    function setAffectations(int $idCarte, array $affectations): void
    {
        $pdo = $this->connexionBaseDeDonnees->getPdo();
        $pdo->query("DELETE FROM etreaffecte
            WHERE idcarte = $idCarte;
        ");

        if ($affectations) {
            $sql = "INSERT INTO etreaffecte (idcarte, login)
                        VALUES ";
    
            $tags = [];
            $j = 0;
            foreach ($affectations as $affectation) {
                $login = $affectation;
                $tags["loginTag$j"] = $login;
                $sql .= "($idCarte, :loginTag$j)";
    
                $j++;
    
                if ($j != count($affectations)) $sql .= ', ';
            }
    
            $prep = $pdo->prepare($sql);
            $prep->execute($tags);
        }
    }

    protected function getNomTable(): string
    {
        return "carte";
    }

    protected function getNomCle(): string
    {
        return "idcarte";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idcarte", "titrecarte", "descriptifcarte", "couleurcarte", "idcolonne"
        ];
    }
}