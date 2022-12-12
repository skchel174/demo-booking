<?php

namespace Tests\Framework\Controller\ArgumentResolver;

use Framework\Controller\ArgumentResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Framework\Controller\DummyController\ArrayController;
use Tests\Framework\Controller\DummyController\InvokableController;

class GetArgumentsTest extends TestCase
{
    /**
     * @dataProvider controllerProvider
     */
    public function testSuccess(callable $controller): void
    {
        $request = new Request(attributes: [
            'name' => $name = 'John Doe',
        ]);
        $resolver = new ArgumentResolver();
        $arguments = $resolver->getArguments($request, $controller);

        $this->assertIsArray($arguments);
        $this->assertNotEmpty($arguments);
        $this->assertEquals($request, $arguments['request']);
        $this->assertEquals($name, $arguments['name']);
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testGetDefaultArgument(callable $controller): void
    {
        $request = new Request();
        $resolver = new ArgumentResolver();
        $arguments = $resolver->getArguments($request, $controller);

        $this->assertEquals('John Doe', $arguments['name']);
    }

    public function testGetNotProvidedArgument(): void
    {
        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Controller for URI "%s" requires that you provide a value for the "%s" argument.',
            $request->getPathInfo(),
            'name',
        ));

        $controller = fn(string $name) => new Response();
        $resolver = new ArgumentResolver();
        $resolver->getArguments($request, $controller);
    }

    private function controllerProvider(): array
    {
        return [
            'array controller' => [[new ArrayController(), 'index']],
            'invokable controller' => [new InvokableController()],
            'callback controller' => [fn(Request $request, string $name = 'John Doe') => new Response()],
        ];
    }
}
