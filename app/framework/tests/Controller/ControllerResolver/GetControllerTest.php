<?php

namespace Framework\Tests\Controller\ControllerResolver;

use Framework\Controller\ControllerResolver;
use Framework\Tests\Controller\DummyController\ArrayController;
use Framework\Tests\Controller\DummyController\InvokableController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetControllerTest extends TestCase
{
    private ContainerInterface $container;
    private ControllerResolver $resolver;

    protected function setUp(): void
    {
        $this->container = $this
            ->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $this->resolver = new ControllerResolver($this->container);
    }

    /**
     * @dataProvider parametersProvider
     */
    public function testSuccess(array $parameters, ?object $result): void
    {
        $this->container
            ->method('get')
            ->willReturn($result);

        $request = new Request(attributes: $parameters);
        $controller = $this->resolver->getController($request);

        $this->assertIsCallable($controller);
        $this->assertInstanceOf(Response::class, $controller($request));
    }

    public function testEmptyRequestParameters(): void
    {
        $controller = $this->resolver->getController(new Request());

        $this->assertFalse($controller);
    }

    public function testNotExistsClass(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" not exists.', $controller = 'InvalidController')
        );

        $this->container
            ->method('get')
            ->willReturn(null);

        $request = new Request(attributes: ['_controller' => $controller]);
        $this->resolver->getController($request);
    }

    public function testNotExistsMethod(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" does not have a method "%s"', ArrayController::class, 'method')
        );

        $this->container
            ->method('get')
            ->willReturn(new ArrayController());

        $request = new Request(attributes: ['_controller' => [ArrayController::class, 'method']]);
        $this->resolver->getController($request);
    }

    public function testResolveNotInvokable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" is not callable', ArrayController::class)
        );

        $this->container
            ->method('get')
            ->willReturn(new ArrayController());

        $request = new Request(attributes: ['_controller' => ArrayController::class]);
        $this->resolver->getController($request);
    }

    private function parametersProvider(): array
    {
        return [
            'callback controller' => [['_controller' => fn(Request $request) => new Response()], null],
            'invokable controller' => [['_controller' => InvokableController::class], new InvokableController()],
            'string controller' => [['_controller' => ArrayController::class . '::index'], new ArrayController()],
            'array controller' => [['_controller' => [ArrayController::class, 'index']], new ArrayController()],
        ];
    }
}
