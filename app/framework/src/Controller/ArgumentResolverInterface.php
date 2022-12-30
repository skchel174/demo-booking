<?php

namespace Framework\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ArgumentResolverInterface
{
    /**
     * @param Request $request
     * @param callable $controller
     * @return array
     */
    public function getArguments(Request $request, callable $controller): array;
}
