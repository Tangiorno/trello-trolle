<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;

class TableauRepository extends AbstractRepository implements ITableauRepository
{
    public function __construct(
        private readonly IConnexionBaseDeDonnees $connexionBaseDeDonnees,
        private readonly IUtilisateurRepository  $utilisateurRepository,
    )
    {
        parent::__construct($this->connexionBaseDeDonnees);
    }

    public function recupererTableauxUtilisateur(string $login): array
    {
//        return $this->recupererPlusieursPar("loginproprietaire", $login);
        $sql = "SELECT * FROM tableau WHERE loginproprietaire = :loginPropTag";
        $prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $prep->execute([
            'loginPropTag' => $login
        ]);
        $objs = [];
        foreach ($prep as $tab) {
            $objs[] = $this->construireDepuisTableau($tab);
        }
        return $objs;
    }

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject
    {
        return $this->recupererPar("codetableau", $codeTableau);
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from tableau
                WHERE loginproprietaire = :loginTag
                   OR idtableau IN (
                       SELECT idtableau
                       FROM participer
                       where login = :loginTag
                   );";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            "loginTag" => $login
        ]);

        $tableaux = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $tableaux[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $tableaux;
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        $objetFormatTableau["proprietaire"] = $this->utilisateurRepository->recupererParClefPrimaire($objetFormatTableau["loginproprietaire"]);
        $objetFormatTableau["participants"] = $this->utilisateurRepository->getUtilisateursParticipantsA($objetFormatTableau["idtableau"]);
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from tableau
                where idtableau in (
                       SELECT idtableau
                       FROM participer
                       where login = :loginTag
                   );";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            "loginTag" => $login
        ]);

        $tableaux = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $tableaux[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $tableaux;
    }

    public function getNombreTableauxTotalUtilisateur(string $login): int
    {
        $sql = "SELECT COUNT(DISTINCT idtableau) as count FROM tableau WHERE loginproprietaire = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();

        return $obj['count'];
    }

    public function ajouterParticipantAtableau(int $idTableau, string $login): void
    {
        $sql = "INSERT INTO participer
                VALUES ($idTableau, :loginTag)";
        $prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $prep->execute([
            'loginTag' => $login
        ]);
    }

    function retirerParticipant(int $idTableau, string $login): void
    {
        $sql = "DELETE FROM participer
                WHERE idtableau = $idTableau
                    AND login = :loginTag;
        ";
        $prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $prep->execute([
            'loginTag' => $login
        ]);
    }

    protected function getNomTable(): string
    {
        return "tableau";
    }

    protected function getNomCle(): string
    {
        return "idtableau";
    }

    protected function getNomsColonnes(): array
    {
        return ["idtableau", "codetableau", "titretableau", "loginproprietaire"];
    }
}