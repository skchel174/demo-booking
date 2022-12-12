<?php

namespace Tests\Framework\Controller\DummyController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArrayController
{
    public function index(Request $request, string $name = 'John Doe'): Response
    {
        return new Response();
    }
}
