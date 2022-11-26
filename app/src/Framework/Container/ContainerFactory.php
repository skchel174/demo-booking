<?php

namespace App\Framework\Container;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    public function __construct(private readonly string $appDir)
    {}

    /**
     * @param bool $debug
     * @return Container
     * @throws Exception
     */
    public function __invoke(bool $debug): Container
    {
        if ($debug) {
            return $this->createNewContainer();
        }
        return $this->getContainerFromCache();
    }

    /**
     * @return ContainerBuilder
     * @throws Exception
     */
    private function createNewContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $servicesLocator = new FileLocator($this->appDir . '/config');
        $servicesLoader = new YamlFileLoader($container, $servicesLocator);
        $servicesLoader->load('services.yaml');
        $container->compile();

        return $container;
    }

    /**
     * @return Container
     * @throws Exception
     */
    private function getContainerFromCache(): Container
    {
        $dump = $this->appDir . '/var/cache/container.php';

        if (!file_exists($dump)) {
            $container = $this->createNewContainer();
            $this->dumpContainer($container, $dump);
        }

        require_once $dump;

        return new \ProjectServiceContainer();
    }

    /**
     * @param ContainerBuilder $container
     * @param string $filename
     * @return void
     */
    private function dumpContainer(ContainerBuilder $container, string $filename): void
    {
        $dumper = new PhpDumper($container);
        file_put_contents($filename, $dumper->dump());
    }
}
