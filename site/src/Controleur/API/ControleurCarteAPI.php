<?php

namespace App\Trellotrolle\Controleur\API;

use App\Trellotrolle\Controleur\ControleurGenerique;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceCarte;
use App\Trellotrolle\Service\IServiceColonne;
use App\Trellotrolle\Service\IServiceTableau;
use App\Trellotrolle\Service\IServiceUtilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurCarteAPI extends ControleurGenerique
{

    public function __construct(
        private readonly ContainerInterface    $container,
        private readonly IServiceCarte         $serviceCarte,
        private readonly IServiceColonne       $serviceColonne,
        private readonly IServiceTableau       $serviceTableau,
        private readonly IServiceUtilisateur   $serviceUtilisateur,
        private readonly IConnexionUtilisateur $connexionUtilisateur
    )
    {
        parent::__construct($container);
    }

    #[Route(path: '/api/carte/{idCarte}', name: 'supprimerCarte', methods: ['DELETE'])]
    public function supprimer($idCarte): Response
    {
        $idUtilisateurConnecte = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
        try {
            $this->serviceCarte->supprimerCarte($idCarte, $idUtilisateurConnecte);
        } catch (ServiceException $e) {
            return $this->respError($e);
        }
        return new JsonResponse('', Response::HTTP_OK);
    }

    #[Route(path: '/api/carte/{idCarte}', name: 'mettreAJourCarte', methods: ['PATCH'])]
    public function mettreAJourCarte(Request $request, $idCarte): Response
    {
        try {
            $_REQUEST = (array)json_decode($request->getContent());

            $this->serviceColonne->checkConnexionEtAreSet(["idColonne"]);

            $carte = $this->serviceCarte->getCarte($idCarte);

            $colonne = $this->serviceColonne->getColonneById($_REQUEST["idColonne"]);

            $this->serviceCarte->checkIssetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"]);

            $this->serviceTableau->comparerTableauxDeColonnes($carte->getColonne(), $colonne);

            $tableau = $colonne->getTableau();
            $this->serviceTableau->checkUtilisateurCoEstParticipantOuProprietaire($tableau);

            $affectations = [];
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                $utilisateur = $this->serviceUtilisateur->getUserById($affectation);

                $this->serviceTableau->checkParticipantOuProprietaire($tableau, $utilisateur->getLogin());

                $affectations[] = $utilisateur;
            }
            $this->serviceCarte->mettreAJour($carte, $colonne, $_REQUEST["titreCarte"], $_REQUEST["descriptifCarte"], $_REQUEST["couleurCarte"], $affectations);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => json_encode($e->getMessage())], 500);
        }
        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/api/carte/{idCarte}', name: 'afficherDetailCarte', methods: ['GET'])]
    public function afficherDetail($idCarte): Response
    {
        $carte = $this->serviceCarte->getCarte($idCarte);
        return new JsonResponse($carte);
    }

    #[Route(path: '/api/carte', name: 'ajouterCarte', methods: ['POST'])]
    public function ajouterCarte(Request $request): Response
    {
        try {
            $donnees = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idColonne = $donnees->idColonne ?? null;
            $titre = $donnees->titre ?? "";
            $descriptif = $donnees->descriptif ?? "";
            $couleur = $donnees->couleur ?? null;
            $affectation = $donnees->affectation ?? [];

            $carte = $this->serviceCarte->creerCarte($idColonne, $titre, $descriptif, $couleur, $affectation);

            try {
                $this->serviceUtilisateur->checkConnexion();
                $this->serviceTableau->checkUtilisateurCoEstParticipantOuProprietaire($carte->getColonne()->getTableau());
                $estParticipantOuProprio = true;
            } catch (ServiceException $e) {
                $estParticipantOuProprio = false;
            }

            return new JsonResponse([
                'carte' => $carte->jsonSerialize(),
                'estCoEtParticipe' => $estParticipantOuProprio,
            ], Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], 500);
        } catch (\JsonException) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

}