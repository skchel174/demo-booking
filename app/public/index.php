<?php

declare(strict_types=1);

use Framework\Kernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Kernel(true))->handle(Request::createFromGlobals())->send();
