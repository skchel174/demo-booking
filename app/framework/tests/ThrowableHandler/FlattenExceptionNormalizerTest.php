<?php

namespace Framework\Tests\ThrowableHandler;

use Framework\ThrowableHandler\ExtendedHttpException;
use Framework\ThrowableHandler\FlattenExceptionNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class FlattenExceptionNormalizerTest extends TestCase
{
    public function testNormalizeException(): void
    {
        $exception = new RuntimeException('Test exception');
        $flattenException = $this->createFlattenExceptionMock($exception);

        $normalizer = new FlattenExceptionNormalizer(false);
        $data = $normalizer->normalize($flattenException);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('message', $data);
    }

    public function testNormalizeExceptionWithDebugMode(): void
    {
        $exception = new RuntimeException('Test exception');
        $flattenException = $this->createFlattenExceptionMock($exception, true);

        $normalizer = new FlattenExceptionNormalizer(true);
        $data = $normalizer->normalize($flattenException);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('class', $data);
        $this->assertArrayHasKey('line', $data);
        $this->assertArrayHasKey('trace', $data);
    }

    private function createFlattenExceptionMock(\Throwable $exception, bool $debug = false): FlattenException|MockObject
    {
        $flattenException = $this->createMock(FlattenException::class);

        $flattenException->expects($this->once())
            ->method('getMessage')
            ->willReturn($exception->getMessage());

        if ($debug) {
            $flattenException->expects($this->once())
                ->method('getClass')
                ->willReturn(ExtendedHttpException::class);

            $previousException = $this->createMock(FlattenException::class);

            $previousException->expects($this->once())
                ->method('getClass')
                ->willReturn($exception::class);
            $previousException->expects($this->once())
                ->method('getLine')
                ->willReturn($exception->getLine());
            $previousException->expects($this->once())
                ->method('getTrace')
                ->willReturn($exception->getTrace());

            $flattenException->expects($this->once())
                ->method('getPrevious')
                ->willReturn($previousException);
        } else {
            $flattenException->expects($this->never())
                ->method('getPrevious');
        }

        return $flattenException;
    }
}
