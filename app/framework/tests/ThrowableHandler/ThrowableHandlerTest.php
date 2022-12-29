<?php

namespace Framework\Tests\ThrowableHandler;

use Framework\ThrowableHandler\ExtendedHttpException;
use Framework\ThrowableHandler\ThrowableHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

class ThrowableHandlerTest extends TestCase
{
    public function testHandleExceptionWithoutConfig(): void
    {
        $exception = new RuntimeException($message = 'Test exception');

        $logger = $this->createLoggerMock('error', $message);
        $renderer = $this->createRendererMock($exception, $statusCode = 500);
        $handler = new ThrowableHandler($renderer, $logger, []);

        $response = $handler->handle($exception, $this->createMock(Request::class));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertStringContainsString($message, $response->getContent());
    }

    public function testHandleExceptionWithConfig(): void
    {
        $exception = new RuntimeException($message = 'Test exception');

        $exceptionMapping = [
            RuntimeException::class => [
                'status_code' => $statusCode = 403,
                'loggable' => true,
                'log_level' => $logLevel = 'notice',
            ],
        ];

        $logger = $this->createLoggerMock($logLevel, $message);
        $renderer = $this->createRendererMock($exception, $statusCode);
        $handler = new ThrowableHandler($renderer, $logger, $exceptionMapping);

        $response = $handler->handle($exception, $this->createMock(Request::class));

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testHandleNotLoggableException(): void
    {
        $exception = new RuntimeException('Test exception');

        $exceptionMapping = [
            RuntimeException::class => [
                'status_code' => $statusCode = 403,
                'loggable' => false,
            ],
        ];

        $renderer = $this->createRendererMock($exception, $statusCode);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('log');
        $handler = new ThrowableHandler($renderer, $logger, $exceptionMapping);

        $handler->handle($exception, $this->createMock(Request::class));
    }

    public function testHandleSymfonyHttpException(): void
    {
        $exception = new SymfonyHttpException($statusCode = 500, $message  = 'Test exception', null, $headers = [
            'X-Exception-Name' => SymfonyHttpException::class,
        ]);

        $logger = $this->createLoggerMock('error', $message);
        $renderer = $this->createRendererMock($exception, $statusCode, $headers);
        $handler = new ThrowableHandler($renderer, $logger, []);

        $response = $handler->handle($exception, $this->createMock(Request::class));

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals(SymfonyHttpException::class, $response->headers->get('X-Exception-Name'));
    }

    public function testAddJsonRenderer(): void
    {
        $exception = new RuntimeException('Test exception');

        $logger = $this->createLoggerMock('error', $exception->getMessage());

        $defaultRenderer = $this->createMock(ErrorRendererInterface::class);
        $defaultRenderer->expects($this->never())
            ->method('render');

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getPreferredFormat')
            ->willReturn('json');

        $handler = new ThrowableHandler($defaultRenderer, $logger);
        $jsonRenderer = $this->createRendererMock($exception, 500);
        $handler->addRenderer('json', $jsonRenderer);

        $handler->handle($exception, $request);
    }

    private function createLoggerMock(string $logLevel, string $message): LoggerInterface
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with($logLevel, $this->stringContains($message), $this->arrayHasKey('trace'));

        return $logger;
    }

    private function createRendererMock(\Throwable $exception, int $statusCode, array $headers = []): ErrorRendererInterface
    {
        $flattenException = $this->createMock(FlattenException::class);
        $flattenException->expects($this->once())
            ->method('getAsString')
            ->willReturn($exception->getMessage());
        $flattenException->expects($this->once())
            ->method('getStatusCode')
            ->willReturn($statusCode);
        $flattenException->expects($this->once())
            ->method('getHeaders')
            ->willReturn($headers);

        $renderer = $this->createMock(ErrorRendererInterface::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with($this->callback(fn($subject) => $subject instanceof ExtendedHttpException))
            ->willReturn($flattenException);

        return $renderer;
    }
}
