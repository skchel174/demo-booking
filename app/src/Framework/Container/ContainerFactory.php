<?php

namespace App\Framework\Container;

use Exception;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    public function __construct(private readonly bool $debug)
    {}

    /**
     * @return Container
     * @throws Exception
     */
    public function __invoke(): Container
    {
        $cacheFile = BASE_DIR . '/var/cache/container.php';
        $configCache = new ConfigCache($cacheFile, $this->debug);

        if (!$configCache->isFresh()) {
            $container = $this->createNewContainer();
            $dumper = new PhpDumper($container);
            $configCache->write($dumper->dump(), $container->getResources());
        }

        require_once $cacheFile;

        return new \ProjectServiceContainer();
    }

    /**
     * @return ContainerBuilder
     * @throws Exception
     */
    private function createNewContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $servicesLocator = new FileLocator(BASE_DIR . '/config');
        $servicesLoader = new YamlFileLoader($container, $servicesLocator);
        $servicesLoader->load('services.yaml');

        $container->setParameter('debug', $this->debug);

        $container->compile();

        return $container;
    }
}
