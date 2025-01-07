<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\AttributeRouteControllerLoader;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\U;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class RouteurURL
{
    /**
     * @param Request $requete
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public static function traiterRequete(Request $requete): Response
    {
        $conteneur = new ContainerBuilder();
        $conteneur->set('container', $conteneur);
        $conteneur->setParameter('project_root', __DIR__ . '/../..');
        $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__ . "/../Configuration"));
        $loader->load("conteneur.yml");

        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = (new AttributeDirectoryLoader($fileLocator, $attrClassLoader))->load(__DIR__);
        $conteneur->set('routes', $routes);

        $contexteRequete = (new RequestContext())->fromRequest($requete);
        $conteneur->set('request_context', $contexteRequete);
        $twig = $conteneur->get('twig');
        $generateurUrl = $conteneur->get("url_generator");
        $assistantUrl = $conteneur->get("url_helper");
        $twig->addFunction(new TwigFunction("route", [$generateurUrl, "generate"]));
        $twig->addFunction(new TwigFunction("asset", [$assistantUrl, "getAbsoluteUrl"]));
        $twig->addGlobal('connexionUtilisateur', new ConnexionUtilisateurSession());
        $twig->addGlobal("messagesFlash", new MessageFlash());
        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $reponse = call_user_func_array($controleur, $arguments);
        } catch (ResourceNotFoundException $exception) {
            U::pd("" . $exception);
            $reponse = ($conteneur->get('controleur_generique'))->afficherErreur($exception->getMessage(), 404);
        } catch (MethodNotAllowedHttpException $exception) {
            U::pd("method not allowed : " . $exception);
            $reponse = ($conteneur->get('controleur_generique'))->afficherErreur($exception->getMessage(), 405);
        } catch (Exception $exception) {
            U::pd("autre exception : " . $exception);
            $reponse = ($conteneur->get('controleur_generique'))->afficherErreur($exception->getMessage(), 500);
        }

        return $reponse;
    }
}