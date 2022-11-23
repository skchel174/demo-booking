<?php

namespace App\Controller;

use App\Service\DemoService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    private DemoService $service;

    public function __construct(DemoService $service)
    {
        $this->service = $service;
    }

    public function index(): Response
    {
        return new Response("<h1>Home</h1>");
    }

    public function greeting(Request $request): Response
    {
        $name = $request->attributes->get('name') ?: 'World';
        $greeting = $this->service->greeting($name);

        return new Response("<h1>$greeting</h1>");
    }
}
