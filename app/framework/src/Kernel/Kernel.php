<?php

namespace Framework\Kernel;

use Exception;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Throwable;

class Kernel implements KernelInterface
{
    private string $projectDir;
    private ?ContainerInterface $container = null;
    private array $bundles = [];

    public function __construct(private readonly string $environment, private readonly bool $debug)
    {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function handle(Request $request): Response
    {
        $this->boot();
        /** @var HttpKernel $kernel */
        $kernel = $this->container->get(HttpKernel::class);

        return $kernel->handle($request);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    protected function boot(): void
    {
        if ($this->container === null) {
            $this->initializeBundles();
            $this->initializeContainer();
        }

        foreach ($this->bundles as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getProjectDir(): string
    {
        if (empty($this->projectDir)) {
            $class = new ReflectionClass($this::class);
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
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getLogsDir(): string
    {
        return $this->getProjectDir() . '/var/log';
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
            'kernel.environment' => $this->environment,
            'kernel.project_dir' => $this->getProjectDir(),
            'kernel.config_dir' => $this->getConfigDir(),
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogsDir(),
        ];
    }

    /**
     * @param ContainerBuilder $container
     * @return void
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     * @throws ReflectionException
     * @throws Exception
     */
    protected function configureContainer(ContainerBuilder $container): void
    {
        $configDir = $this->getConfigDir();
        $servicesLoader = new YamlFileLoader($container, new FileLocator());

        $servicesLoader->import($configDir . '/packages/*.yaml');
        if (is_dir($configDir . '/packages/' . $this->environment)) {
            $servicesLoader->import($configDir . '/packages/' . $this->environment . '/*.yaml');
        }

        $servicesLoader->import($configDir . '/services.yaml');
        if (is_file($configDir . '/services_' . $this->environment . '.yaml')) {
            $servicesLoader->import($configDir . '/services_' . $this->environment . '.yaml');
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function initializeBundles(): void
    {
        $bundlesList = require $this->getConfigDir() . '/bundles.php';

        $this->bundles = [];
        foreach ($bundlesList as $bundleClass) {
            /** @var BundleInterface $bundle */
            $bundle = new $bundleClass();
            if (isset($this->bundles[$bundle->getName()])) {
                throw new LogicException(sprintf('Bundle with name %s is already registered.', $bundle->getName()));
            }
            $this->bundles[$bundle->getName()] = $bundle;
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function initializeContainer(): void
    {
        $file = $this->getCacheDir() . '/' . $this->getContainerClass() . '.php';
        $cache = new ConfigCache($file, $this->debug);

        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($container, $cache);
        }

        require_once $file;
        $containerClass = $this->getContainerClass();
        $this->container = new $containerClass();
    }

    /**
     * @return ContainerBuilder
     * @throws Exception
     */
    private function buildContainer(): ContainerBuilder
    {
        $kernelParameters = new ParameterBag($this->getKernelParameters());
        $container = new ContainerBuilder($kernelParameters);

        /** @var BundleInterface $bundle */
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
            }

            if ($this->debug) {
                $container->addObjectResource($bundle);
            }

            $bundle->build($container);
        }

        $extensions = array_map(fn($extension) => $extension->getAlias(), $container->getExtensions());
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));

        $this->configureContainer($container);

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     * @param ConfigCache $cache
     * @return void
     */
    private function dumpContainer(ContainerBuilder $container, ConfigCache $cache): void
    {
        $dumper = new PhpDumper($container);
        $dump = $dumper->dump(['class' => $this->getContainerClass()]);
        $cache->write($dump, $container->getResources());
    }

    /**
     * @return string
     */
    private function getContainerClass(): string
    {
        return ucfirst(strtolower($this->environment)) . ($this->debug ? 'Debug' : '') . 'Container';
    }
}
