<?php

namespace App\Trellotrolle\Controleur\API;

use App\Trellotrolle\Controleur\ControleurGenerique;
use App\Trellotrolle\Lib\IConnexionUtilisateur;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\IServiceUtilisateur;
use JsonException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateurAPI extends ControleurGenerique
{
    public function __construct(private readonly ContainerInterface $container,
                                private readonly IServiceUtilisateur $utilisateurService,
                                private readonly IConnexionUtilisateur $connexionUtilisateur)
    {
        parent::__construct($this->container);
    }


    #[Route(path: '/api/auth', name: 'connexion', methods: ['POST'])]
    public function connecter(Request $request): Response
    {
        try {
            $jsonObject = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $login = $jsonObject['login'];
            $password = $jsonObject['password'];
            $this->utilisateurService->checkMdpCorrect($password, $this->utilisateurService->getUserById($login)->getMdpHache());
            $this->connexionUtilisateur->connecter($login);
            return new JsonResponse();
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], 500);
        } catch (JsonException) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}