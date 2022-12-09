<?php

namespace Framework\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ControllerResolverInterface
{
    /**
     * @param Request $request
     * @return callable|false
     */
    public function getController(Request $request): callable|false;
}
