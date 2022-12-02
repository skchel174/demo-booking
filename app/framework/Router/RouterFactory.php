<?php

namespace Framework\Router;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

class RouterFactory
{
    public function __invoke(Container $container): Router
    {
        $locator = new FileLocator($container->getParameter('kernel.config_dir'));
        $loader = new YamlFileLoader($locator);

        return new Router($loader, 'routes.yaml', [
            'debug' => $container->getParameter('kernel.debug'),
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
        ]);
    }
}
