<?php

declare(strict_types=1);

use App\Framework\Container\ContainerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

define('BASE_DIR', dirname(__DIR__));

const DEBUG = true;

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $containerFactory = new ContainerFactory(DEBUG);
    $container = $containerFactory();

    // Initialize Router
    $requestContext = new RequestContext();
    $requestContext->fromRequest($request = Request::createFromGlobals());
    /** @var Router $router */
    $router = $container->get(Router::class);
    $router->setContext($requestContext);

    // Routing
    $parameters = $router->match($requestContext->getPathInfo());
    $request->attributes->add($parameters);
    [$controllerName, $method] = explode('::', $parameters['_controller']);
    $controller = $container->get($controllerName);

    // Call handler
    /** @var Response $response */
    $response = $controller->$method($request);
    echo $response->getContent();
} catch (ResourceNotFoundException $e) {
    echo '<h1>404 Not Found</h1>';
}
