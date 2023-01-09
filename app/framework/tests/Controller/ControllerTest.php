<?php

namespace Framework\Tests\Controller;

use Monolog\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ControllerTest extends TestCase
{
    public function __invoke(Request $request, string $name = 'John Doe'): Response
    {
        return new Response();
    }

    public function index(Request $request, string $name = 'John Doe'): Response
    {
        return new Response();
    }

    public function getCallbackController(): callable
    {
        return fn(Request $request, string $name = 'John Doe') => new Response();
    }

    private function requestParameterProvider(): array
    {
        return [
            'invokable controller' => [['_controller' => static::class]],
            'array controller' => [['_controller' => [static::class, 'index']]],
            'string controller' => [['_controller' => static::class . '::index']],
            'callback controller' => [['_controller' => $this->getCallbackController()]],
        ];
    }

    private function controllerProvider(): array
    {
        return [
            'array controller' => [[$this, 'index']],
            'invokable controller' => [$this],
            'callback controller' => [$this->getCallbackController()],
        ];
    }
}
