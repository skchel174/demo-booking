<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    public function index(): Response
    {
        return new Response('<h1>Home</h1>');
    }

    public function demo(Request $request): Response
    {
        $id = $request->attributes->get('id');

        return new Response("<h1>Demo#$id</h1>");
    }
}
