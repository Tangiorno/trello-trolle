<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

abstract class AbstractRepository
{
    public function __construct(private readonly IConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {

    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    protected abstract function getNomTable(): string;

    protected function formatNomsColonnes(): string
    {
        return join(", ", $this->getNomsColonnes());
    }

    protected abstract function getNomsColonnes(): array;

    protected abstract function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;

    public function recupererParClefPrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        $sql = "SELECT * FROM " . $this->getNomTable() . " WHERE " . $this->getNomCle() . "=:Tag ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("Tag" => $valeurClePrimaire);
        $pdoStatement->execute($values);
        $objet = $pdoStatement->fetch();
        if (!$objet) {
            return null;
        }
        return $this->construireDepuisTableau($objet);
    }

    protected abstract function getNomCle(): string;

    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire = :valeurClefPrimaireTag;";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("valeurClefPrimaireTag" => $valeurClePrimaire);
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * @param AbstractDataObject $object
     * @return void
     */
    public function mettreAJour(AbstractDataObject $object): void
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $nomsColonnes = $this->getNomsColonnes();

        $partiesSet = array_map(function ($nomcolonne) {
            return "$nomcolonne = :{$nomcolonne}Tag";
        }, $nomsColonnes);
        $setString = join(', ', $partiesSet);
        $whereString = "$nomClePrimaire = :{$nomClePrimaire}Tag";

        $sql = "UPDATE $nomTable SET $setString WHERE $whereString";
        $req_prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        
        $req_prep->execute($objetFormatTableau);
    }

    /**
     * @param AbstractDataObject $object
     * @return int
     */
    public function ajouter(AbstractDataObject $object): int
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();

        $insertString = '(' . join(', ', $nomsColonnes) . ')';

        $partiesValues = array_map(function ($nomcolonne) {
            return ":{$nomcolonne}Tag";
        }, $nomsColonnes);
        $valueString = '(' . join(', ', $partiesValues) . ')';

        $sql = "INSERT INTO $nomTable $insertString VALUES $valueString";

        $pdo = $this->connexionBaseDeDonnees->getPdo();
        
        $pdoStatement = $pdo->prepare($sql);

        $objetFormatTableau = $object->formatTableau();

        $pdoStatement->execute($objetFormatTableau);
        
        return $pdo->lastInsertId();
    }

    /**
     * @param string $nomAttribut
     * @param $valeur
     * @return AbstractDataObject|null
     */
    protected function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute();
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY :attributsTexteTag, :sensTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("attributsTexteTag" => $attributsTexte, "sensTag" => $sens);
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): array
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                FROM $nomTable
                WHERE (:nomAttributTag = :valeurTag);
        ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array("nomAttributTag" => $nomAttribut, "valeurTag" => $valeur);
        $pdoStatement->execute($values);

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                FROM $nomTable
                WHERE :nomAttributTag = :valeurTag
                ORDER BY :attributsTexteTag, :sensTag;
        ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array(
            "nomAttributTag" => $nomAttribut,
            "valeurTag" => $valeur,
            "attributsTexteTag" => $attributsTexte,
            "sensTag" => $sens
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }
}
