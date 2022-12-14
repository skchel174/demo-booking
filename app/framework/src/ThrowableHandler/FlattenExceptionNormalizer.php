<?php

namespace Framework\ThrowableHandler;

use ArrayObject;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FlattenExceptionNormalizer implements NormalizerInterface
{
    public function __construct(private readonly bool $debug = false)
    {
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return float|int|bool|ArrayObject|array|string|null
     */
    public function normalize(mixed $object, string $format = null, array $context = []): float|int|bool|ArrayObject|array|string|null
    {
        if (!$object instanceof FlattenException) {
            $message = sprintf('The object must implement "%s".', FlattenException::class);
            throw new InvalidArgumentException($message);
        }

        $data = ['message' => $object->getMessage()];
        if ($this->debug) {
            $exception = $object->getClass() === ExtendedHttpException::class ? $object->getPrevious() : $object;
            $data['class'] = $exception->getClass();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTrace();
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof FlattenException;
    }
}
