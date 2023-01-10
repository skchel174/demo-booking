<?php

namespace Framework\Tests\Kernel\HttpKernel;

use Framework\Controller\ArgumentResolverInterface;
use Framework\Controller\ControllerResolverInterface;
use Framework\Event\ControllerArgumentsEvent;
use Framework\Event\ControllerEvent;
use Framework\Event\ExceptionEvent;
use Framework\Event\RequestEvent;
use Framework\Event\ResponseEvent;
use Framework\Kernel\HttpKernel;
use Monolog\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HandleRequestTest extends TestCase
{
    public function testStandardRequestProcessing(): void
    {
        $request = new Request();
        $response = new Response();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($arg) => $arg instanceof RequestEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerArgumentsEvent)],
                [$this->callback(fn($arg) => $arg instanceof ResponseEvent)],
            );

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $controllerResolver->expects($this->once())
            ->method('getController')
            ->willReturn(fn(Request $request) => $response);

        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);
        $argumentsResolver->expects($this->once())
            ->method('getArguments')
            ->willReturn(['request' => $request]);

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $result = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals($response, $result);
    }

    public function testNotFoundController(): void
    {
        $this->expectException(HttpException::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($arg) => $arg instanceof RequestEvent)],
                [$this->callback(fn($arg) => $arg instanceof ExceptionEvent)],
            );

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $controllerResolver->expects($this->once())
            ->method('getController')
            ->willReturn(false);

        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);
        $argumentsResolver->expects($this->never())
            ->method('getArguments');

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $kernel->handle(new Request());
    }

    public function testHandleApplicationException(): void
    {
        $exceptionResponse = new Response();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(5))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($arg) => $arg instanceof RequestEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerArgumentsEvent)],
                [$this->callback(fn($arg) => $arg instanceof ExceptionEvent)],
                [$this->callback(fn($arg) => $arg instanceof ResponseEvent)],
            )
            ->will($this->returnCallback(function ($arg) use ($exceptionResponse) {
                if ($arg instanceof ExceptionEvent) {
                    $arg->setResponse($exceptionResponse);
                }
                return $arg;
            }));

        $request = $this->createMock(Request::class);

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $controllerResolver->expects($this->once())
            ->method('getController')
            ->willReturn(fn(Request $request) => throw new \RuntimeException('Test exception'));

        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);
        $argumentsResolver->expects($this->once())
            ->method('getArguments')
            ->willReturn(['request' => $request]);

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $result = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals($result, $exceptionResponse);
    }

    public function testExecutionCompletionAfterRequestEvent(): void
    {
        $request = new Request();
        $response = new Response();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($arg) => $arg instanceof RequestEvent)],
                [$this->callback(fn($arg) => $arg instanceof ResponseEvent)],
            )
            ->will($this->returnCallback(function ($arg) use ($response) {
                if ($arg instanceof RequestEvent) {
                    $arg->setResponse($response);
                }
                return $arg;
            }));

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $result = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals($result, $response);
    }

    public function testHandleApplicationExceptionWithoutExceptionHandler(): void
    {
        $this->expectException(\RuntimeException::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(fn($arg) => $arg instanceof RequestEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerEvent)],
                [$this->callback(fn($arg) => $arg instanceof ControllerArgumentsEvent)],
                [$this->callback(fn($arg) => $arg instanceof ExceptionEvent)],
            );

        $request = $this->createMock(Request::class);

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $controllerResolver->expects($this->once())
            ->method('getController')
            ->willReturn(fn(Request $request) => throw new \RuntimeException('Test exception'));

        $argumentsResolver = $this->createMock(ArgumentResolverInterface::class);
        $argumentsResolver->expects($this->once())
            ->method('getArguments')
            ->willReturn(['request' => $request]);

        $kernel = new HttpKernel($dispatcher, $controllerResolver, $argumentsResolver);
        $kernel->handle($request);
    }
}
