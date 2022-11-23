<?php

declare(strict_types=1);

use App\Controller\DemoController;
use App\Service\DemoService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $containerBuilder = new ContainerBuilder();

    $containerBuilder->register(DemoService::class, DemoService::class);
    $containerBuilder->register(DemoController::class, DemoController::class)
        ->addArgument(new Reference(DemoService::class));

    $requestContext = new RequestContext();
    $requestContext->fromRequest($request = Request::createFromGlobals());

    $fileLocator = new FileLocator(BASE_DIR);
    $fileLoader = new YamlFileLoader($fileLocator);
    $router = new Router(
        $fileLoader,
        'config/routes.yaml',
        ['cache_dir' => BASE_DIR . '/var/cache'],
        $requestContext,
    );

    $parameters = $router->match($requestContext->getPathInfo());
    $request->attributes->add($parameters);

    [$controllerName, $method] = explode('::', $parameters['_controller']);
    $controller = $containerBuilder->get($controllerName);

    /** @var Response $response */
    $response = $controller->$method($request);
    echo $response->getContent();
} catch (ResourceNotFoundException $e) {
    echo '<h1>404 Not Found</h1>';
}
