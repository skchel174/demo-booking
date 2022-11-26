<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader as RoutesFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader as ServicesFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

define('BASE_DIR', dirname(__DIR__));

const DEBUG = true;

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $configLocator = new FileLocator(BASE_DIR . '/config');

    // Initialize Container
    $file = BASE_DIR . '/var/cache/container.php';
    $cachedContainer = 'CachedContainer';

    if (file_exists($file)) {
        require_once $file;
        $container = new $cachedContainer();
    } else {
        $container = new ContainerBuilder();
        $servicesLoader = new ServicesFileLoader($container, $configLocator);
        $servicesLoader->load('services.yaml');
        $container->compile();

        if (!DEBUG) {
            $dumper = new PhpDumper($container);
            $containerDump = $dumper->dump(['class' => $cachedContainer]);
            file_put_contents($file, $containerDump);
        }
    }

    // Initialize Router
    $requestContext = new RequestContext();
    $requestContext->fromRequest($request = Request::createFromGlobals());

    $routerCache = !DEBUG ? BASE_DIR . '/var/cache' : null;

    $router = new Router(
        new RoutesFileLoader($configLocator),
        'routes.yaml',
        ['cache_dir' => $routerCache],
        $requestContext,
    );

    // Routing
    $parameters = $router->match($requestContext->getPathInfo());
    $request->attributes->add($parameters);

    [$controllerName, $method] = explode('::', $parameters['_controller']);
    $controller = $container->get($controllerName);

    /** @var Response $response */
    $response = $controller->$method($request);
    echo $response->getContent();
} catch (ResourceNotFoundException $e) {
    echo '<h1>404 Not Found</h1>';
}
