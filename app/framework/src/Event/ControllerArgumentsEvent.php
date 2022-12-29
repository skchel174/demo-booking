<?php

namespace Framework\Event;

use Symfony\Component\HttpFoundation\Request;

class ControllerArgumentsEvent extends KernelEvent
{
    private object|array|string $controller;
    private array $arguments;

    public function __construct(
        object|array|string $controller,
        array $arguments,
        Request $request,
    ) {
        parent::__construct($request);

        $this->arguments = $arguments;
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
     * @param array|object|string $controller
     */
    public function setController(object|array|string $controller): void
    {
        $this->controller = $controller;
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
}
