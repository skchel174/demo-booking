<?php

declare(strict_types=1);

use Framework\Kernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

define('BASE_DIR', dirname(__DIR__));

const DEBUG = true;

require_once BASE_DIR . '/vendor/autoload.php';

(new Kernel(DEBUG))->handle(Request::createFromGlobals())->send();
