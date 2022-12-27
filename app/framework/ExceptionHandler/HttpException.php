<?php

namespace Framework\ExceptionHandler;

use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

class HttpException extends SymfonyHttpException
{
    private bool $loggable;
    private string $loglevel;

    /**
     * @return bool
     */
    public function isLoggable(): bool
    {
        return $this->loggable;
    }

    /**
     * @param bool $loggable
     */
    public function setLoggable(bool $loggable): void
    {
        $this->loggable = $loggable;
    }

    /**
     * @return string
     */
    public function getLogLevel(): string
    {
        return $this->loglevel;
    }

    /**
     * @param string $loglevel
     * @return void
     */
    public function setLoglevel(string $loglevel): void
    {
        $this->loglevel = $loglevel;
    }
}
