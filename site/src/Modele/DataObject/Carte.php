<?php

namespace App\Trellotrolle\Modele\DataObject;

class Carte extends AbstractDataObject
{
    /**
     * @param int|null $idCarte
     * @param string $titreCarte
     * @param string $descriptifCarte
     * @param string $couleurCarte
     * @param Colonne $colonne
     * @param Utilisateur[] $affectationsCarte
     */
    public function __construct(
        private ?int           $idCarte,
        private string         $titreCarte,
        private string         $descriptifCarte,
        private string         $couleurCarte,
        private Colonne        $colonne,
        private readonly array $affectationsCarte = [],
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau): Carte
    {
        return new Carte(
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            $objetFormatTableau["colonne"],
            $objetFormatTableau['affectationsCarte']
        );
    }

    public function formatTableau(): array
    {
        return array(
            "idcarteTag" => $this->idCarte,
            "titrecarteTag" => $this->titreCarte,
            "descriptifcarteTag" => $this->descriptifCarte,
            "couleurcarteTag" => $this->couleurCarte,
            "idcolonneTag" => $this->colonne->getIdColonne()
        );
    }


    /**
     * @return ?int
     */
    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    /**
     * @param ?int $idCarte
     */
    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    /**
     * @return string
     */
    public function getTitreCarte(): string
    {
        return $this->titreCarte;
    }

    /**
     * @param string $titreCarte
     */
    public function setTitreCarte(string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    /**
     * @return string
     */
    public function getDescriptifCarte(): string
    {
        return $this->descriptifCarte;
    }

    /**
     * @param string $descriptifCarte
     */
    public function setDescriptifCarte(string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    /**
     * @return string
     */
    public function getCouleurCarte(): string
    {
        return $this->couleurCarte;
    }

    /**
     * @param string $couleurCarte
     */
    public function setCouleurCarte(string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }

    /**
     * @return Colonne
     */
    public function getColonne(): Colonne
    {
        return $this->colonne;
    }

    /**
     * @param Colonne $colonne
     */
    public function setColonne(Colonne $colonne): void
    {
        $this->colonne = $colonne;
    }

    /**
     * @return Utilisateur[]
     */
    public function getAffectationsCarte(): array
    {
        return $this->affectationsCarte;
    }

    public function getAffectationsCarteHTML(): string
    {
        $affs = "";
        foreach ($this->affectationsCarte as $user) {
            $affs .= '<span>' . htmlspecialchars($user->getPrenom()) . ' ' . htmlspecialchars($user->getNom()) . '</span>';
        }
        return $affs;
    }

    public function jsonSerialize(): array
    {
        $arr = [
            "idCarte" => $this->getIdCarte(),
            "titreCarte" => $this->getTitreCarte(),
            "descriptifCarte" => $this->getDescriptifCarte(),
            'couleurCarte' => $this->getCouleurCarte(),
            "affectationsCarte" => [],
            'colonne' => $this->getColonne()->jsonSerialize(),
        ];
        foreach ($this->getAffectationsCarte() as $user) {
            $arr["affectationsCarte"][] = $user->jsonSerialize();
        }
        return $arr;
    }
}