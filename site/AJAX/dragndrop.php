<?php

if (isset($_REQUEST['carte']) && isset($_REQUEST['colonne'])) {
    $serverName = 'webinfo.iutmontp.univ-montp2.fr';
    $user = 'izoretr';
    $passwd = '';
    $databaseName = 'izoretr';
    $conn = new mysqli($serverName, $user, $passwd, $databaseName, 3316);
    if ($conn->connect_error) {
        die("Erreur: " . $conn->connect_error);
    }
    $carte = $_REQUEST['carte'];
    $colonne = $_REQUEST['colonne'];

    $sql = "UPDATE carte SET idcolonne = $colonne WHERE idcarte = $carte;";

    try {
        $res = $conn->query($sql);
        if ($res === TRUE) {
            echo "OK";
        } else {
            throw new PDOException($conn->error);
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
    $conn->close();
} else {
    echo "Mauvaises données";
}
