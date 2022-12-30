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
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\ErrorHandler\ErrorHandler;
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

    public function __construct(private readonly bool $debug)
    {
    }

    public static function registerErrorHandler(bool $debug)
    {
        $errorHandler = new ErrorHandler(new BufferingLogger(), $debug);
        ErrorHandler::register($errorHandler);
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
        return $this->getProjectDir() . '/var/cache';
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
     * @return string
     * @throws ReflectionException
     */
    protected function getPackagesDir(): string
    {
        return $this->getConfigDir() . '/packages';
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getServicesPath(): string
    {
        return $this->getConfigDir() . '/services.yaml';
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getBundlesPath(): string
    {
        return $this->getConfigDir() . '/bundles.php';
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
            'kernel.logs_dir' => $this->getLogsDir(),
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
     * @param ContainerBuilder $container
     * @return void
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     * @throws ReflectionException
     * @throws Exception
     */
    protected function configureContainer(ContainerBuilder $container): void
    {
        $servicesLoader = new YamlFileLoader($container, new FileLocator());
        $servicesLoader->import($this->getPackagesDir() . '/*.yaml');
        $servicesLoader->load($this->getServicesPath());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function initializeBundles(): void
    {
        $bundlesList = require $this->getBundlesPath();

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
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());

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

        $container->compile();

        return $container;
    }
}
