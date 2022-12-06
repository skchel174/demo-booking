<?php

namespace Framework\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ControllerResolver
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return callable|false
     */
    public function getController(Request $request): callable|false
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return false;
        }

        if (is_string($controller)) {
            $controller = explode('::', $controller);
        }

        if (is_array($controller)) {
            $class = $controller[0];
            $method = $controller[1] ?? null;

            if (!$controller = $this->container->get($class)) {
                throw new \RuntimeException(sprintf('The controller "%s" not exists.', $class));
            }

            if ($method && !method_exists($controller, $method)) {
                throw new \RuntimeException(
                    sprintf('The controller "%s" does not have a method "%s"', $controller::class, $method)
                );
            }

            if ($method) {
                return function (Request $request) use ($controller, $method) {
                    return $controller->$method($request);
                };
            }
        }

        if (!is_callable($controller)) {
            throw new \RuntimeException(sprintf('The controller "%s" is not callable', $controller::class));
        }

        return function ($request) use ($controller) {
            return $controller($request);
        };
    }
}
