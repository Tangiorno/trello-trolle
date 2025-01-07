<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\IConfigDB;
use PDO;


class ConnexionBaseDeDonnees implements IConnexionBaseDeDonnees
{
    private PDO $pdo;

    public function __construct(private readonly IConfigDB $configurationBaseDeDonnees)
    {
        $this->pdo = new PDO($this->configurationBaseDeDonnees->getDSN(),
        $this->configurationBaseDeDonnees->getLogin(),
        $this->configurationBaseDeDonnees->getMotDePasse(),
        $this->configurationBaseDeDonnees->getOptions());

    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}