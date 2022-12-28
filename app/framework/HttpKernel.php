<?php

namespace Kernel;

use Framework\bundle\src\Controller\ArgumentResolverInterface;
use Framework\bundle\src\Controller\ControllerResolverInterface;
use Framework\bundle\src\Event\ControllerArgumentsEvent;
use Framework\bundle\src\Event\ControllerEvent;
use Kernel\Event\ExceptionEvent;
use Kernel\Event\RequestEvent;
use Kernel\Event\ResponseEvent;
use Kernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->handleRequest($request);
        } catch (\Throwable $e) {
            return $this->handleThrowable($e, $request);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handleRequest(Request $request): Response
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

        $event = new ControllerArgumentsEvent($controller, $arguments, $request);
        $this->dispatcher->dispatch($event);
        $controller = $event->getController();
        $arguments = $event->getArguments();

        $response = $controller(...$arguments);

        return $this->handleResponse($response, $request);
    }

    /**
     * @param \Throwable $e
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    private function handleThrowable(\Throwable $e, Request $request): Response
    {
        $event = new ExceptionEvent($e, $request);
        $this->dispatcher->dispatch($event);

        if (!$event->hasResponse()) {
            throw $e;
        }

        $response = $event->getResponse();

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
