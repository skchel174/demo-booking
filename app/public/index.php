<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    $fileLocator = new FileLocator([dirname(__DIR__)]);
    $loader = new YamlFileLoader($fileLocator);
    $routes = $loader->load('config/routes.yaml');

    $requestContext = new RequestContext();
    $requestContext->fromRequest($request = Request::createFromGlobals());

    $urlMatcher = new UrlMatcher($routes, $requestContext);
    $parameters = $urlMatcher->match($requestContext->getPathInfo());
    $request->attributes->add($parameters);

    [$controllerName, $method] = explode('::', $parameters['_controller']);
    $controller = new $controllerName();
    /** @var Response $response */
    $response = $controller->$method($request);
    echo $response->getContent();
} catch (ResourceNotFoundException $e) {
    echo '<h1>404 Not Found</h1>';
}
