<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;

class ControllerArgumentsEvent
{
    private array $arguments;
    private object|array|string $controller;
    private Request $request;

    public function __construct(array $arguments, object|array|string $controller, Request $request)
    {
        $this->arguments = $arguments;
        $this->controller = $controller;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array|object|string
     */
    public function getController(): object|array|string
    {
        return $this->controller;
    }

    /**
     * @param array|object|string $controller
     */
    public function setController(object|array|string $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
