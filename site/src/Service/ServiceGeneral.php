<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\U;
use App\Trellotrolle\Service\Exception\ServiceException;

class ServiceGeneral implements IServiceGeneral
{

    public function __construct(
        private readonly IConnexionUtilisateur $connexionUtilisateur
    )
    {
    }

    public function checkConnexionEtAreSet(array $attribute): void
    {
        $this->checkConnexion();
        $this->checkIssetAndNotNull($attribute);
    }

    public function checkIssetAndNotNull(array $requestParams): void
    {
        foreach ($requestParams as $param) {
            if (!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                throw new ServiceException("Données manquantes.");
            }
        }
    }

    public function checkIssetAndNotNullForObjectsArray(array $objects): void
    {
        foreach ($objects as $object) {
            if (!(isset($object) && $object != null)) {
                throw new ServiceException("Données manquantes.");
            }
        }
    }

    function checkConnexion(): void
    {
        if (!$this->connexionUtilisateur->estConnecte()) {
            throw new ServiceException("Il faut être connecté pour réaliser cette action.");
        }
    }

}
