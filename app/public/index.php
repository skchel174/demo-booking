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
if (!isset($_ENV['APP_DEBUG'])) {
    $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = $_ENV['APP_ENV'] === 'prod' ? 0 : 1;
}

$handler = new ErrorHandler(new BufferingLogger());
ErrorHandler::register($handler, (bool)$_ENV['APP_DEBUG']);

$kernel = new Kernel($_ENV['APP_ENV'], (bool)$_ENV['APP_DEBUG']);
$response = $kernel->handle(Request::createFromGlobals());
$response->send();
