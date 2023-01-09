<?php

namespace Framework\ThrowableHandler;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class HtmlRenderer implements ErrorRendererInterface
{
    public function __construct(private readonly HtmlErrorRenderer $renderer, private readonly bool $debug)
    {
    }

    /**
     * @param Throwable $exception
     * @return FlattenException
     */
    public function render(Throwable $exception): FlattenException
    {
        if ($this->debug && $exception instanceof ExtendedHttpException) {
            $exception = $exception->getPrevious() ?: $exception;
        }
        return $this->renderer->render($exception);
    }
}