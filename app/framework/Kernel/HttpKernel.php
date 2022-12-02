<?php

namespace Framework\Kernel;

use Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class HttpKernel implements KernelInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);
        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);
        $router->setContext($requestContext);

        $parameters = $router->match($requestContext->getPathInfo());
        $request->attributes->add($parameters);

        [$controllerName, $method] = explode('::', $parameters['_controller']);
        $controller = $this->container->get($controllerName);

        return $controller->$method($request);
    }
}
