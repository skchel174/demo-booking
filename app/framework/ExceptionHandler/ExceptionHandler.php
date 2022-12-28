<?php

namespace Framework\ExceptionHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface
{
    private array $renderers = [];

    public function __construct(
        private readonly ErrorRendererInterface $defaultRenderer,
        private readonly ?LoggerInterface $logger = null,
        private readonly array $exceptionMapping = [],
    ) {}

    /**
     * @param Throwable $e
     * @param Request $request
     * @return Response
     */
    public function handle(Throwable $e, Request $request): Response
    {
        $e = $this->configureException($e);

        if ($e->isLoggable()) {
            $this->log($e->getPrevious(), $e->getLogLevel());
        }

        $renderer = $this->resolveRenderer($request);
        $exception = $renderer->render($e);

        return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * @param string $format
     * @param ErrorRendererInterface $renderer
     * @return void
     */
    public function addRenderer(string $format, ErrorRendererInterface $renderer): void
    {
        $this->renderers[$format] = $renderer;
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
     * @param Throwable $e
     * @return HttpException
     */
    private function configureException(Throwable $e): HttpException
    {
        $config = $this->getExceptionConfig($e);

        $headers = [];
        if ($e instanceof HttpExceptionInterface) {
            $headers = $e->getHeaders();
            if (!isset($config['status_code'])) {
                $config['status_code'] = $e->getStatusCode();
            }
        }

        $statusCode = $config['status_code'] ?? 500;
        $isLoggable = $config['loggable'] ?? true;
        $logLevel = $config['log_level'] ?? 'error';

        $exception = new HttpException($statusCode, $e->getMessage(), $e, $headers, $e->getCode());
        $exception->setLoggable($isLoggable);
        $exception->setLoglevel($logLevel);

        return $exception;
    }

    /**
     * @param Throwable $e
     * @return array
     */
    private function getExceptionConfig(Throwable $e): array
    {
        foreach ($this->exceptionMapping as $class => $config) {
            if (!isset($config['status_code'])) {
                continue;
            }

            if ($e::class === $class || is_subclass_of($e, $class)) {
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
        $format = $request->getPreferredFormat();
        return $this->renderers[$format] ?? $this->defaultRenderer;
    }
}
