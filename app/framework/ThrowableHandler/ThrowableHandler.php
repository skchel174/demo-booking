<?php

namespace Framework\ThrowableHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ThrowableHandler implements ThrowableHandlerInterface
{
    private array $renderers = [];

    public function __construct(
        private readonly ErrorRendererInterface $defaultRenderer,
        private readonly LoggerInterface $logger,
        private readonly array $exceptionMapping = [],
    )
    {
    }

    /**
     * @param Throwable $throwable
     * @param Request $request
     * @return Response
     */
    public function handle(Throwable $throwable, Request $request): Response
    {
        $config = $this->getThrowableConfig($throwable);
        $exception = ExtendedHttpException::fromThrowable($throwable, $config);

        if ($exception->isLoggable()) {
            $this->log($exception->getPrevious(), $exception->getLogLevel());
        }

        $renderer = $this->resolveRenderer($request);
        $exception = $renderer->render($exception);

        return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * @param string $type
     * @param ErrorRendererInterface $renderer
     * @return void
     */
    public function addRenderer(string $type, ErrorRendererInterface $renderer): void
    {
        $this->renderers[$type] = $renderer;
    }

    /**
     * @param Throwable $exception
     * @param string $logLevel
     * @return void
     */
    private function log(Throwable $exception, string $logLevel): void
    {
        $message = sprintf(
            '%s: "%s" at %s line %s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        );
        $this->logger->log($logLevel, $message, ['trace' => $exception->getTraceAsString()]);
    }

    /**
     * @param Throwable $throwable
     * @return array
     */
    private function getThrowableConfig(Throwable $throwable): array
    {
        foreach ($this->exceptionMapping as $class => $config) {
            if (is_a($throwable, $class)) {
                return $config;
            }
        }
        return [];
    }

    /**
     * @param Request $request
     * @return ErrorRendererInterface
     */
    private function resolveRenderer(Request $request): ErrorRendererInterface
    {
        return $this->renderers[$request->getPreferredFormat()] ?? $this->defaultRenderer;
    }
}
