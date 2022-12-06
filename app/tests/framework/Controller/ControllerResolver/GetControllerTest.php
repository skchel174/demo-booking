<?php

use Framework\Controller\ControllerResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetControllerTest extends TestCase
{
    private ContainerInterface $container;
    private ControllerResolver $resolver;

    protected function setUp(): void
    {
        $this->container = $this
            ->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $this->resolver = new ControllerResolver($this->container);
    }

    /**
     * @dataProvider parametersProvider
     */
    public function testSuccess(array $parameters, object|callable $result): void
    {
        $this->container
            ->method('get')
            ->willReturn($result);

        $request = new Request();
        $request->attributes->add($parameters);
        $controller = $this->resolver->getController($request);

        $this->assertIsCallable($controller);
        $this->assertInstanceOf(Response::class, $controller($request));
    }

    public function testEmptyRequestParameters(): void
    {
        $controller = $this->resolver->getController(new Request());

        $this->assertFalse($controller);
    }

    public function testNotExistsClass(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" not exists.', $controller = 'InvalidController')
        );

        $this->container
            ->method('get')
            ->willReturn(null);

        $request = new Request();
        $request->attributes->add(['_controller' => $controller]);
        $this->resolver->getController($request);
    }

    public function testNotExistsMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" does not have a method "%s"', DummyController::class, 'method')
        );

        $this->container
            ->method('get')
            ->willReturn(new DummyController());

        $request = new Request();
        $request->attributes->add(['_controller' => [DummyController::class, 'method']]);
        $this->resolver->getController($request);
    }

    public function testResolveNotInvokable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The controller "%s" is not callable', DummyController::class)
        );

        $this->container
            ->method('get')
            ->willReturn(new DummyController());

        $request = new Request();
        $request->attributes->add(['_controller' => DummyController::class]);
        $this->resolver->getController($request);
    }

    private function parametersProvider(): array
    {
        $callbackController = fn(Request $request) => new Response();

        return [
            'callback controller' => [['_controller' => $callbackController], $callbackController],
            'invokable controller' => [['_controller' => InvokableController::class], new InvokableController()],
            'string controller' => [['_controller' => DummyController::class . '::index'], new DummyController()],
            'array controller' => [['_controller' => [DummyController::class, 'index']], new DummyController()],
        ];
    }
}

class DummyController
{
    public function index(Request $request): Response
    {
        return new Response();
    }
}

class InvokableController
{
    public function __invoke(Request $request): Response
    {
        return new Response();
    }
}
