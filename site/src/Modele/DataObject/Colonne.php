<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\ITableauRepository;

class Colonne extends AbstractDataObject
{
    public function __construct(
        private ?int    $idColonne,
        private string  $titreColonne,
        private Tableau $tableau
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau, ITableauRepository $tableauRepository): Colonne
    {
        return new Colonne(
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
            $tableauRepository->recupererParClefPrimaire($objetFormatTableau["idtableau"]),
        );
    }

    public function formatTableau(): array
    {
        return array(
            "idcolonneTag" => $this->idColonne,
            "titrecolonneTag" => $this->titreColonne,
            "idtableauTag" => $this->tableau->getIdTableau()
        );
    }


    /**
     * @return ?int
     */
    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    /**
     * @param ?int $idColonne
     */
    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    /**
     * @return string
     */
    public function getTitreColonne(): string
    {
        return $this->titreColonne;
    }

    /**
     * @param string $titreColonne
     */
    public function setTitreColonne(string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    /**
     * @return Tableau
     */
    public function getTableau(): Tableau
    {
        return $this->tableau;
    }

    /**
     * @param Tableau $tableau
     */
    public function setTableau(Tableau $tableau): void
    {
        $this->tableau = $tableau;
    }

    public function jsonSerialize(): array
    {
        return [
            "idColonne" => $this->getIdColonne(),
            "titreColonne" => $this->getTitreColonne(),
            'tableau' => $this->getTableau()->jsonSerialize()
        ];
    }
}
