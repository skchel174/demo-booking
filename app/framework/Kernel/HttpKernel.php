<?php

namespace Framework\Kernel;

use Exception;
use Framework\Event\RequestEvent;
use Framework\Event\ResponseEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class HttpKernel implements KernelInterface
{
    private Container $container;
    private EventDispatcher $dispatcher;

    public function __construct(Container $container, EventDispatcher $dispatcher)
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        $event = new RequestEvent($request);
        $this->dispatcher->dispatch($event);

        if ($event->hasResponse()) {
            return $this->handleResponse($event->getResponse(), $request);
        }

        /** @var Router $router */
        $router = $this->container->get(Router::class);
        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);
        $router->setContext($requestContext);

        $parameters = $router->match($requestContext->getPathInfo());
        $request->attributes->add($parameters);

        [$controllerName, $method] = explode('::', $parameters['_controller']);
        $controller = $this->container->get($controllerName);

        $response = $controller->$method($request);

        return $this->handleResponse($response, $request);
    }

    /**
     * @param Response $response
     * @param Request $request
     * @return Response
     */
    private function handleResponse(Response $response, Request $request): Response
    {
        $event = new ResponseEvent($request, $response);
        $this->dispatcher->dispatch($event);
        return $event->getResponse();
    }
}
