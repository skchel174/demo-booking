<?php

namespace App\Service;

class DemoService
{
    public function greeting(string $name): string
    {
        return "Hello, $name!";
    }
}
