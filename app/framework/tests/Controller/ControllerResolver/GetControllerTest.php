<?php

namespace Framework\Tests\Controller\ControllerResolver;

use Framework\Controller\ControllerResolver;
use Framework\Tests\Controller\ControllerTest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetControllerTest extends ControllerTest
{
    /**
     * @dataProvider requestParameterProvider
     */
    public function testSuccess(array $parameters): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($this);
        $resolver = new ControllerResolver($container);

        $request = new Request(attributes: $parameters);
        $controller = $resolver->getController($request);

        $this->assertIsCallable($controller);
        $this->assertInstanceOf(Response::class, $controller($request));
    }

    public function testEmptyRequestParameters(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($this);
        $resolver = new ControllerResolver($container);

        $controller = $resolver->getController(new Request());

        $this->assertFalse($controller);
    }

    public function testNotExistsClass(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" not exists.', $controller = 'InvalidController')
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(null);
        $resolver = new ControllerResolver($container);

        $request = new Request(attributes: ['_controller' => $controller]);
        $resolver->getController($request);
    }

    public function testNotExistsMethod(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" does not have a method "%s"', $this::class, 'method')
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($this);
        $resolver = new ControllerResolver($container);

        $request = new Request(attributes: ['_controller' => [$this::class, 'method']]);
        $resolver->getController($request);
    }

    public function testResolveNotInvokable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" is not callable', \stdClass::class)
        );

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(new \stdClass());
        $resolver = new ControllerResolver($container);

        $request = new Request(attributes: ['_controller' => $this::class]);
        $resolver->getController($request);
    }
}
