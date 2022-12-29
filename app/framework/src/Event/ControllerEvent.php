<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;

class ControllerEvent extends KernelEvent
{
    private object|array|string $controller;

    public function __construct(object|array|string $controller, Request $request)
    {
        parent::__construct($request);

        $this->controller = $controller;
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
}
