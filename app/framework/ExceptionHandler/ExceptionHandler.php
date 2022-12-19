<?php

namespace Framework\ExceptionHandler;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface
{
    private array $renderers = [];

    public function __construct(private readonly ErrorRendererInterface $defaultRenderer)
    {}

    /**
     * {@inheritdoc}
     */
    public function handleException(Throwable $exception, Request $request): Response
    {
        $contentType = $request->headers->get('Accept') ?? $request->headers->get('Content-Type');

        $renderer = $this->resolveRenderer($contentType);
        $exception = $renderer->render($exception);

        return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * @param string $contentType
     * @param ErrorRendererInterface $renderer
     * @return void
     */
    public function addRenderer(string $contentType, ErrorRendererInterface $renderer): void
    {
        $this->renderers[$contentType] = $renderer;
    }

    /**
     * @param string $contentType
     * @return ErrorRendererInterface
     */
    private function resolveRenderer(string $contentType): ErrorRendererInterface
    {
        if (isset($this->renderers[$contentType])) {
            return $this->renderers[$contentType];
        }
        return $this->defaultRenderer;
    }
}
