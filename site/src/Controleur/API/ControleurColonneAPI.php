<?php

namespace App\Trellotrolle\Controleur\API;

use App\Trellotrolle\Controleur\ControleurGenerique;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceColonne;
use App\Trellotrolle\Service\IServiceTableau;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonneAPI extends ControleurGenerique
{
    public function __construct(
        private readonly ContainerInterface    $container,
        private readonly IServiceColonne       $colonneService,
        private readonly IServiceTableau       $serviceTableau,
        private readonly IConnexionUtilisateur $connexionUtilisateur
    )
    {
        parent::__construct($container);
    }

    #[Route(path: '/api/colonne/{idColonne}', name: 'supprimerColonne', methods: ['DELETE'])]
    public function supprimer($idColonne): Response
    {
        $idUtilisateurConnecte = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
        try {
            $this->colonneService->supprimerColonne($idColonne, $idUtilisateurConnecte);
        } catch (ServiceException $e) {
            return $this->respError($e);
        }
        return new JsonResponse('', Response::HTTP_OK);
    }

    #[Route(path: '/api/colonne/{idColonne}', name: 'mettreAJourColonne', methods: ['PATCH'])]
    public function mettreAJourColonne(Request $request, $idColonne): Response
    {
        try {
            $_REQUEST = (array)json_decode($request->getContent());

            $this->colonneService->checkConnexion();

            $colonne = $this->colonneService->getColonneById($idColonne);
            $this->colonneService->checkIssetAndNotNullForObjectsArray([$colonne]);
            $this->colonneService->checkIssetAndNotNull(["titreColonne"]);

            $tableau = $colonne->getTableau();
            $this->serviceTableau->checkUtilisateurCoEstParticipantOuProprietaire($tableau);

            $this->colonneService->mettreAJour($_REQUEST["titreColonne"], $colonne);
        } catch (ServiceException $e) {
            return $this->respError($e);
        }
        return new JsonResponse('', Response::HTTP_OK);
    }

    #[Route(path: '/api/colonne', name: 'ajouterColonne', methods: ['POST'])]
    public function ajouterColonne(Request $request): Response
    {
        try {
            $donnees = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau = $donnees->idTableau ?? null;
            $titre = $donnees->titre ?? "";

            $colonne = $this->colonneService->creerColonne($idTableau, $titre);

            try {
                $this->colonneService->checkConnexion();
                $this->serviceTableau->checkUtilisateurCoEstParticipantOuProprietaire($colonne->getTableau());
                $estParticipantOuProprio = true;
            } catch (ServiceException) {
                $estParticipantOuProprio = false;
            }

            return new JsonResponse([
                'colonne' => $colonne,
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