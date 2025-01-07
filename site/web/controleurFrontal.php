<?php

////////////////////
// Initialisation //
////////////////////

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

\App\Trellotrolle\Modele\HTTP\Session::getInstance();

/////////////
// Routage //
/////////////
///
///
$requete = Request::createFromGlobals();
try {
    App\Trellotrolle\Controleur\RouteurURL::traiterRequete($requete)->send();
} catch (Exception $e) {
    echo "Website crashed in frontalController !! Error : " . $e;
}

