<?php

namespace Framework\ThrowableHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ThrowableHandlerInterface
{
    /**
     * @param Throwable $throwable
     * @param Request $request
     * @return Response
     */
    public function handle(Throwable $throwable, Request $request): Response;
}
