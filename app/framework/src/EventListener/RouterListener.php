<?php

namespace Framework\EventListener;

use Framework\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class RouterListener
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param RequestEvent $event
     * @return void
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);
        $this->router->setContext($requestContext);

        try {
            $parameters = $this->router->match($requestContext->getPathInfo());
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('Not route found for %s %s', $request->getMethod(), $request->getUri());
            throw new HttpException(404, $message, $e);
        }

        $request->attributes->add($parameters);
    }
}
