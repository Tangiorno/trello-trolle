<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject
{
    /**
     * @param int|null $idTableau
     * @param string $codeTableau
     * @param string $titreTableau
     * @param Utilisateur $proprietaire
     * @param Utilisateur[] $participants
     */
    public function __construct(
        private ?int           $idTableau,
        private string         $codeTableau,
        private string         $titreTableau,
        private Utilisateur    $proprietaire,
        private readonly array $participants = []
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau): Tableau
    {
        return new Tableau(
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            $objetFormatTableau["proprietaire"],
            $objetFormatTableau["participants"]
        );
    }

    public function estParticipantOuProprietaire(string $login): bool
    {
        if ($this->proprietaire->getLogin() === $login) {
            return true;
        }

        foreach ($this->participants as $participant) {
            if ($participant->getLogin() === $login) {
                return true;
            }
        }

        return false;
    }

    public function formatTableau(): array
    {
        return array(
            "idtableauTag" => $this->idTableau,
            "codetableauTag" => $this->codeTableau,
            "titretableauTag" => $this->titreTableau,
            "loginproprietaireTag" => $this->proprietaire->getLogin()
        );
    }

    /**
     * @return ?int
     */
    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    /**
     * @param ?int $idTableau
     */
    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    /**
     * @return string
     */
    public function getCodeTableau(): string
    {
        return $this->codeTableau;
    }

    /**
     * @param string $codeTableau
     */
    public function setCodeTableau(string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    /**
     * @return string
     */
    public function getTitreTableau(): string
    {
        return $this->titreTableau;
    }

    /**
     * @param string $titreTableau
     */
    public function setTitreTableau(string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    /**
     * @return Utilisateur
     */
    public function getProprietaire(): Utilisateur
    {
        return $this->proprietaire;
    }

    /**
     * @param Utilisateur $proprietaire
     */
    public function setProprietaire(Utilisateur $proprietaire): void
    {
        $this->proprietaire = $proprietaire;
    }

    /**
     * @return Utilisateur[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function jsonSerialize(): array
    {
        $arr = [
            "idTableau" => $this->getIdTableau(),
            "titreTableau" => $this->getTitreTableau(),
            "proprietaire" => $this->getProprietaire()->jsonSerialize(),
        ];
        $participantsJSON = [];
        foreach ($this->getParticipants() as $participant) {
            $participantsJSON[] = $participant->jsonSerialize();
        }
        $arr["participants"] = $participantsJSON;
        return $arr;
    }
}