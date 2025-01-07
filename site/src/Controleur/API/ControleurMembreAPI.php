<?php

namespace App\Trellotrolle\Controleur\API;

use App\Trellotrolle\Controleur\ControleurGenerique;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceCarte;
use App\Trellotrolle\Service\IServiceTableau;
use App\Trellotrolle\Service\IServiceUtilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurMembreAPI extends ControleurGenerique
{
    public function __construct(
        private readonly ContainerInterface  $container,
        private readonly IServiceTableau     $serviceTableau,
        private readonly IServiceCarte       $serviceCarte,
        private readonly IServiceUtilisateur $serviceUtilisateur,
        private readonly IConnexionUtilisateur $connexionUtilisateur,
    )
    {
        parent::__construct($container);
    }

    #[Route(path: '/api/membre/{codeTableau}/supprimer/{login}', name: 'supprimerMembre', methods: ['DELETE'])]
    public function supprimer($codeTableau, $login): Response
    {
        try {
            $this->serviceTableau->checkConnexion();
            $tableau = $this->serviceTableau->getTableauByCode($codeTableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::rediriger("accueil");
        }
        try {
            $utilisateur = $this->serviceUtilisateur->getUserById($login);
            $this->serviceTableau->checkUtilisateurCoEstProprietaire($tableau);
            $this->serviceTableau->retirerParticipant($tableau, $login);
            $idsCartes = $this->serviceCarte->enleverUtilisateurDeAffectationsDeTableau($login, $tableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::rediriger("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        return new JsonResponse([$utilisateur->getPrenom(), $utilisateur->getNom(), $idsCartes], Response::HTTP_OK);
    }

    #[Route(path: '/api/membre', name: 'ajouterMembreAPI', methods: ['PATCH'])]
    public function ajouterMembre(Request $request): Response
    {
        try {
            $donnees = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idTableau = $donnees->idTableau ?? null;
            $login = $donnees->membre ?? null;

            $this->serviceUtilisateur->checkConnexion();
            
            $membre = $this->serviceUtilisateur->getUserById($login);
            /** @var Tableau $tableau */
            $tableau = $this->serviceTableau->getTableauById($idTableau);
            $this->serviceTableau->checkIssetAndNotNullForObjectsArray([$tableau, $membre]);
            $this->serviceTableau->checkUtilisateurCoEstProprietaire($tableau);
            
            $this->serviceTableau->ajouterMembre($tableau, $membre);

            return new JsonResponse([
                'membre' => $membre->jsonSerialize(),
                'codeTableau' => $tableau->getCodeTableau(),
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