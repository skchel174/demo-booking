<?php

namespace Tests\Framework\Controller\DummyController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvokableController
{
    public function __invoke(Request $request, string $name = 'John Doe'): Response
    {
        return new Response();
    }
}
