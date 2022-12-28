<?php

namespace Framework\ExceptionHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $e
     * @param Request $request
     * @return Response
     */
    public function handle(Throwable $e, Request $request): Response;
}
