<?php

namespace Framework\Controller;

use Symfony\Component\HttpFoundation\Request;

class ArgumentResolver implements ArgumentResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, callable $controller): array
    {
        $arguments = [];

        $requestAttributes = $this->getRequestAttributes($request);
        $controllerParameters = $this->getControllerParameters($controller);

        foreach ($controllerParameters as $parameter) {
            $name = $parameter->getName();

            if (isset($requestAttributes[$name])) {
                $arguments[$name] = $requestAttributes[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[$name] = $parameter->getDefaultValue();
                continue;
            }

            throw new \InvalidArgumentException(sprintf(
                'Controller for URI "%s" requires that you provide a value for the "%s" argument.',
                $request->getPathInfo(),
                $parameter->getName(),
            ));
        }

        return $arguments;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getRequestAttributes(Request $request): array
    {
        return array_merge($request->attributes->all(), ['request' => $request]);
    }

    /**
     * @param callable $controller
     * @return array
     * @throws \ReflectionException
     */
    private function getControllerParameters(callable $controller): array
    {
        if (is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $reflection = new \ReflectionMethod($controller, '__invoke');
        } else {
            $reflection = new \ReflectionFunction($controller);
        }

        return $reflection->getParameters();
    }
}
