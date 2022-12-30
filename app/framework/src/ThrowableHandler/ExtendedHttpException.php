<?php

namespace Framework\ThrowableHandler;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExtendedHttpException extends HttpException
{
    private bool $loggable;
    private string $loglevel;

    /**
     * @param Throwable $throwable
     * @param array $options
     * @return ExtendedHttpException
     */
    public static function fromThrowable(Throwable $throwable, array $options = []): ExtendedHttpException
    {
        $headers = [];
        if ($throwable instanceof HttpExceptionInterface) {
            $headers = $throwable->getHeaders();
            if (!isset($options['status_code'])) {
                $options['status_code'] = $throwable->getStatusCode();
            }
        }
        $statusCode = $options['status_code'] ?? 500;
        $isLoggable = $options['loggable'] ?? true;
        $logLevel = $options['log_level'] ?? 'error';

        $exception = new ExtendedHttpException(
            $statusCode,
            $throwable->getMessage(),
            $throwable,
            $headers,
            $throwable->getCode(),
        );
        $exception->setLoggable($isLoggable);
        $exception->setLoglevel($logLevel);

        return $exception;
    }

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
