<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;

class ColonneRepository extends AbstractRepository implements IColonneRepository
{
    public function __construct(
        private readonly IConnexionBaseDeDonnees $connexionBaseDeDonnees,
        private readonly ITableauRepository      $tableauRepository,
    )
    {
        parent::__construct($this->connexionBaseDeDonnees);
    }

    /**
     * @param int $idTableau
     * @return Colonne[]
     */
    public function recupererColonnesTableau(int $idTableau): array
    {
        //return $this->recupererPlusieursPar("idtableau", $idTableau);
        $sql = "SELECT * FROM colonne WHERE idtableau = :idTableauTag";
        $prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $prep->execute([
            'idTableauTag' => $idTableau
        ]);
        $objs = [];
        foreach ($prep as $col) {
            $objs[] = Colonne::construireDepuisTableau($col, $this->tableauRepository);
        }
        return $objs;
    }

    public function getNombreColonnesTotalTableau(int $idTableau): int
    {
        $sql = "SELECT COUNT(DISTINCT idcolonne) as count FROM colonne WHERE idtableau = :idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj['count'];
    }

    protected function getNomTable(): string
    {
        return "colonne";
    }

    protected function getNomCle(): string
    {
        return "idcolonne";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idcolonne", "titrecolonne", "idtableau"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Colonne::construireDepuisTableau($objetFormatTableau, $this->tableauRepository);
    }

}