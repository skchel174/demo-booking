<?php

namespace Framework\Tests\Kernel\HttpKernel;

use Framework\Controller\ArgumentResolverInterface;
use Framework\Controller\ControllerResolverInterface;
use Framework\Event\TerminateEvent;
use Framework\Kernel\HttpKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TerminateTest extends TestCase
{
    public function testTerminate(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->withConsecutive([$this->callback(fn($arg) => $arg instanceof TerminateEvent)]);

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $kernel->terminate(new Request(), new Response());
    }
}