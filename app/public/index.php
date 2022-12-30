<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$envManager = new Dotenv();
$envManager->loadEnv(dirname(__DIR__) . '/.env');

ErrorHandler::register(new ErrorHandler(new BufferingLogger(), (bool)$_SERVER['APP_DEBUG']));

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$response = $kernel->handle(Request::createFromGlobals());
$response->send();
