<?php

namespace App\Trellotrolle\Configuration;

use PDO;

/** Données de configuration de la BD (sous le MariaDB de webinfo) */
class ConfigurationDB implements IConfigDB
{
    private const HOSTNAME = "webinfo.iutmontp.univ-montp2.fr";
    private const DB_NAME = "izoretr";
    private const PORT = "3316";
    private const LOGIN = "izoretr";
    private const PASSWORD = '';

    public function getLogin(): string
    {
        return $this::LOGIN;
    }
    public function getMotDePasse(): string
    {
        return $this::PASSWORD;
    }
    public function getDSN() : string{
        return "mysql:host=" . self::HOSTNAME . ";port=" . self::PORT . ";dbname=" . self::DB_NAME;
    }
    public function getOptions() : array {
        // Option pour que toutes les chaines de caractères
        // en entrée et sortie de MySql soit dans le codage UTF-8
        return array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
    }
}
