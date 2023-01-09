<?php

namespace Framework\Router;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

class RouterFactory
{
    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly mixed $resource
    )
    {
    }

    /**
     * @param Container $container
     * @return Router
     */
    public function __invoke(Container $container): Router
    {
        return new Router($this->loader, $this->resource, [
            'debug' => $container->getParameter('kernel.debug'),
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
        ]);
    }
}
