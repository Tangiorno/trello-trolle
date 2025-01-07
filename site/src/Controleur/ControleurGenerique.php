<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ControleurGenerique
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }
    
    /** @return Response réponse HTTP */
    protected function afficherVue(string $subVue, array $parametres = []): Response
    {
        $parametres['messagesFlash'] = MessageFlash::lireTousMessages();
        $parametres['loginUtilisateurConnecte'] = (new ConnexionUtilisateurSession)->getLoginUtilisateurConnecte();
        $parametres['estConnecte'] = (new ConnexionUtilisateurSession)->estConnecte();

        return self::afficherTwig($subVue . '.html.twig', $parametres);
    }

    /**
     * @param string $cheminVue
     * @param array $parametres
     * @return Response
     */
    private function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        try {
            $corpsReponse = $twig->render($cheminVue, $parametres);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            echo "Twig error in $cheminVue : " . $e->getMessage();
            die();
        }
        return new Response($corpsReponse);
    }

    protected function rediriger(string $nomRoute, array $parameters = []): RedirectResponse
    {
        /** @var UrlGenerator $generateurUrl */
        $generateurUrl = $this->container->get('url_generator');
        /** @var UrlHelper $assistantUrl */
        $assistantUrl = $this->container->get('url_helper');

        $url = $assistantUrl->getAbsoluteUrl($generateurUrl->generate($nomRoute, $parameters));
        return new RedirectResponse($url);
    }

    #[Route(path: '/', name: 'accueil', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->afficherVue('base/accueil');
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        return self::afficherVue('erreur', [
            "messageErreur" => $messageErreurVue
        ]);
    }

    protected function respError(ServiceException $e): JsonResponse
    {
        return new JsonResponse(["error" => $e->getMessage()], 500);
    }
}
