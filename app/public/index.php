<?php

declare(strict_types=1);

use App\Controller\DemoController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    $indexRoute = new Route('/', [
        '_controller' => DemoController::class,
        '_method' => 'index',
    ]);

    $demoRoute = new Route('/demo/{id}', [
        '_controller' => DemoController::class,
        '_method' => 'demo',
    ], ['id' => '\d+']);

    $routes = new RouteCollection();
    $routes->add('home', $indexRoute);
    $routes->add('demo', $demoRoute);

    $requestContext = new RequestContext();
    $requestContext->fromRequest($request = Request::createFromGlobals());

    $urlMatcher = new UrlMatcher($routes, $requestContext);
    $parameters = $urlMatcher->match($requestContext->getPathInfo());
    $request->attributes->add($parameters);

    $controllerName = $parameters['_controller'];
    $method = $parameters['_method'];
    $controller = new $controllerName();
    /** @var Response $response */
    $response = $controller->$method($request);
    echo $response->getContent();
} catch (ResourceNotFoundException $e) {
    echo '<h1>404 Not Found</h1>';
}
