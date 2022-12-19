<?php

namespace Framework\ExceptionHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $exception
     * @param Request $request
     * @return Response
     */
    public function handleException(Throwable $exception, Request $request): Response;
}
