<?php

namespace Framework\Router;

use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

class RoutesResource
{
    public function __construct(private readonly string $configDir)
    {
    }

    /**
     * @param string $env
     * @return RouteCollection
     * @throws FileLoaderImportCircularReferenceException|LoaderLoadException
     */
    public function __invoke(string $env): RouteCollection
    {
        $routes = new RouteCollection();
        $loader = new YamlFileLoader(new FileLocator());

        $collection = $loader->import($this->configDir . '/routes.yaml');
        $routes->addCollection($collection);

        if (is_dir($this->configDir . '/routes/' . $env)) {
            $collection = $loader->import($this->configDir . '/routes/' . $env . '/*.yaml');
            $routes->addCollection($collection);
        }

        if (is_dir($this->configDir . '/routes')) {
            $collection = $loader->import($this->configDir . '/routes/*.yaml');
            $routes->addCollection($collection);
        }

        return $routes;
    }
}
