<?php

declare(strict_types=1);

use Framework\Kernel\Kernel;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

const DEBUG = true;
//const DEBUG = false;

$errorHandler = new ErrorHandler(new BufferingLogger(), DEBUG);
ErrorHandler::register($errorHandler);

$kernel = new Kernel('dev', DEBUG);
$response = $kernel->handle(Request::createFromGlobals());
$response->send();
