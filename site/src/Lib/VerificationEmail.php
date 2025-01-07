<?php
namespace App\Trellotrolle\Lib;
use App\Trellotrolle\Configuration\ConfigurationSite;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

class VerificationEmail{

     public static function envoyerMailMdpOublie(Utilisateur $entreprise): void
    {
        $lienValidationEmail = ConfigurationSite::url() . 'web/utilisateur/resetMDP/'.$entreprise->getLogin();
        $corpsEmail = "<p>Bonjour ".$entreprise->getLogin() ."<p></p><h2>Vous avez demandé la réinitialisation de votre mot de passe de l'application trello trollé.</h2><p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe.</p><a href=\"$lienValidationEmail\">MODIFIER LE MOT DE PASSE</a>";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8-general-ci';
        $headers[] = 'From: adresse_expéditeur@example.com';
        if (mail($entreprise->getEmail(), "Reinitialisation de mot de passe", VerificationEmail::squeletteCorpsMail("MOT DE PASSE OUBLIE", $corpsEmail, $entreprise->getLogin()), implode("\r\n", $headers))) {
            MessageFlash::ajouter("success", "Un email vous a bien été envoyé");
        } else {
            MessageFlash::ajouter("danger", "nope email");
            $error = error_get_last();
            if ($error !== null) {
                echo "Erreur lors de l'envoi de l'e-mail : " . $error['message'];
            }
        }
    }

    public static function squeletteCorpsMail(string $titre, string $message): string
    {
        return '
        <html lang="fr">
            <head>
                
            <title>tkt</title></head>
            <body>
            <div>
               
                  <h1>' . $titre . '</h1>
                  </div>
                
            
                <div>
                    ' . $message . '
                </div>
           
            </body>
        </html>
        ';
    }
}