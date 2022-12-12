<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ControllerEvent extends Event
{
    private object|array|string $controller;
    private Request $request;

    public function __construct(object|array|string $controller, Request $request)
    {
        $this->controller = $controller;
        $this->request = $request;
    }

    /**
     * @return array|object|string
     */
    public function getController(): object|array|string
    {
        return $this->controller;
    }

    /**
     * @param object|array|string $controller
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
}
