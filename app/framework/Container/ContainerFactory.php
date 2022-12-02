<?php

namespace Framework\Container;

use Exception;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    /**
     * @param bool $debug
     * @return Container
     * @throws Exception
     */
    public function __invoke(bool $debug): Container
    {
        $cacheFile = BASE_DIR . '/var/cache/container.php';
        $configCache = new ConfigCache($cacheFile, $debug);

        if (!$configCache->isFresh()) {
            $container = $this->createNewContainer($debug);
            $dumper = new PhpDumper($container);
            $configCache->write($dumper->dump(), $container->getResources());
        }

        require_once $cacheFile;

        return new \ProjectServiceContainer();
    }

    /**
     * @param bool $debug
     * @return ContainerBuilder
     * @throws Exception
     */
    private function createNewContainer(bool $debug): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $servicesLocator = new FileLocator(BASE_DIR);
        $servicesLoader = new YamlFileLoader($container, $servicesLocator);

        $servicesLoader->load('framework/config/services.yaml');
        $servicesLoader->load('config/services.yaml');

        $container->setParameter('debug', $debug);

        $container->compile();

        return $container;
    }
}
