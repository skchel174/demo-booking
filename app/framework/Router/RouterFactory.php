<?php

namespace Framework\Router;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

class RouterFactory
{
    public function __invoke(bool $debug): Router
    {
        $locator = new FileLocator(BASE_DIR . '/config');
        $loader = new YamlFileLoader($locator);

        return new Router($loader, 'routes.yaml', [
            'debug' => $debug,
            'cache_dir' => BASE_DIR . '/var/cache',
        ]);
    }
}
