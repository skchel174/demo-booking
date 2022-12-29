<?php

namespace Framework\EventListener;

use Framework\Event\RequestEvent;
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

        $parameters = $this->router->match($requestContext->getPathInfo());

        $request->attributes->add($parameters);
    }
}
