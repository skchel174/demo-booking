<?php

namespace Framework\Kernel;

use Exception;
use Framework\Controller\ArgumentResolverInterface;
use Framework\Controller\ControllerResolverInterface;
use Framework\Event\ControllerEvent;
use Framework\Event\RequestEvent;
use Framework\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Kernel\Exception\NotFoundHttpException;

class HttpKernel implements KernelInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ControllerResolverInterface $controllerResolver,
        private readonly ArgumentResolverInterface $argumentResolver,
    ) {}

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

        $request = $event->getRequest();

        if (!$controller = $this->controllerResolver->getController($request)) {
            throw new NotFoundHttpException(sprintf(
                'Not found controller for path "%s". The route is wrongly configured.',
                $request->getPathInfo()
            ));
        }

        $event = new ControllerEvent($controller, $request);
        $this->dispatcher->dispatch($event);
        $controller = $event->getController();

        $arguments = $this->argumentResolver->getArguments($request, $controller);

        $response = $controller(...$arguments);

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
