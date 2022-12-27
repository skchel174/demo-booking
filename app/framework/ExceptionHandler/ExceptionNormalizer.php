<?php

namespace Framework\ExceptionHandler;

use ArrayObject;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface
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
            throw new InvalidArgumentException(
                sprintf('The object must implement "%s".', FlattenException::class)
            );
        }

        $data = ['message' => $object->getMessage()];

        if ($this->debug) {
            $exception = $object->getPrevious() ?: $object;
            $data['class'] = $exception->getClass();
            $data['details'] = $exception->getTrace();
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
