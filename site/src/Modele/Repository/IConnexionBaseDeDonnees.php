<?php

namespace App\Trellotrolle\Modele\Repository;

use PDO;

interface IConnexionBaseDeDonnees
{
    public function getPdo(): PDO;
}