<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

dd(Request::createFromGlobals());
