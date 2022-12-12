<?php

namespace Framework\Kernel;

use Exception;
use ReflectionException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Kernel implements KernelInterface
{
    private bool $debug;
    private string $projectDir;
    private ContainerInterface $container;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function handle(Request $request): Response
    {
        if (empty($this->container)) {
            $this->initializeContainer();
        }

        /** @var HttpKernel $kernel */
        $kernel = $this->container->get(HttpKernel::class);

        return $kernel->handle($request);
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getProjectDir(): string
    {
        if (empty($this->projectDir)) {
            $class = new \ReflectionClass($this::class);
            $path = explode('/', $class->getFileName());
            $projectDir = '/' . $path[1];

            if (file_exists($projectDir . '/composer.json')) {
                $this->projectDir = $projectDir;
            }
        }
        return $this->projectDir;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache';
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    protected function getKernelParameters(): array
    {
        return [
            'kernel.debug' => $this->debug,
            'kernel.project_dir' => $this->getProjectDir(),
            'kernel.config_dir' => $this->getConfigDir(),
            'kernel.cache_dir' => $this->getCacheDir(),
        ];
    }

    /**
     * @return string
     */
    protected function getContainerClass(): string
    {
        return 'ProjectServiceContainer';
    }

    /**
     * @throws ReflectionException
     */
    private function initializeContainer(): void
    {
        $cacheFile = $this->getCacheDir() . '/container.php';
        $configCache = new ConfigCache($cacheFile, $this->debug);

        if (!$configCache->isFresh()) {
            $container = $this->buildContainer();
            $dumper = new PhpDumper($container);
            $dump = $dumper->dump(['class' => $this->getContainerClass()]);
            $configCache->write($dump, $container->getResources());
        }

        require_once $cacheFile;

        $containerClass = $this->getContainerClass();
        $this->container = new $containerClass();
    }

    /**
     * @return ContainerBuilder
     * @throws Exception
     */
    private function buildContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $servicesLocator = new FileLocator('/app');
        $servicesLoader = new YamlFileLoader($containerBuilder, $servicesLocator);

        $containerBuilder->getParameterBag()->add($this->getKernelParameters());
        $servicesLoader->load($this->getProjectDir() . '/framework/config/services.yaml');
        $servicesLoader->load($this->getConfigDir() . '/services.yaml');

        $containerBuilder->addCompilerPass(new RegisterListenersPass());

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
