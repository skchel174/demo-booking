<?php

declare(strict_types=1);

use Framework\Kernel;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

const DEBUG = true;
//const DEBUG = false;

ErrorHandler::register(new ErrorHandler(new BufferingLogger(), DEBUG));

(new Kernel(DEBUG))->handle(Request::createFromGlobals())->send();
