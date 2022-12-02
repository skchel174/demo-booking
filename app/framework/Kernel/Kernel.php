<?php

namespace Framework\Kernel;

use Framework\Container\ContainerFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Kernel implements KernelInterface
{
    private bool $debug;
    private Container $container;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            if (empty($this->container)) {
                $containerFactory = new ContainerFactory();
                $this->container = $containerFactory($this->debug);
            }
            /** @var HttpKernel $kernel */
            $kernel = $this->container->get(HttpKernel::class);
            return $kernel->handle($request);
        } catch (Throwable $e) {
            if ($this->debug) {
                dd($e);
            }
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
