<?php

namespace Velox\Router\Tests;

use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Response\TextResponse;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Velox\Router\Exceptions\NoResponseException;
use Velox\Router\Exceptions\NoRouteFoundException;
use Velox\Router\Method;
use Velox\Router\Router;
use Velox\Router\RouteRunner;

class RouterTest extends TestCase {
    protected Router $router;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->router = new Router();

        $this->router->get('/', static function (RouteRunner $route): void {
            $route->response = new TextResponse('Hello, World!');
        });

        $this->router->get('/{id:\d+}', static function (RouteRunner $route): void {
            $route->response = new TextResponse('ID: ' . $route->getParam('id'));
        });

        $this->router->get('/no-response', static fn () => null);

        $this->router->get('/{slot}', static function (RouteRunner $route): void {
            $route->response = new TextResponse('Slot: ' . $route->getParam('slot'));
        });
    }

    public function mockRequest(string $uri, Method $method = Method::GET): ?ResponseInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method->value, $uri);

        return $this->router->resolveRoute($request)?->run() ?? null;
    }

    public function testRootUri(): void
    {
        $response = $this->mockRequest('/');

        $body = $response->getBody()->getContents();

        $this->assertSame('Hello, World!', $body);
    }

    public function testRegexParameter(): void
    {
        $response = $this->mockRequest('/123');

        $body = $response->getBody()->getContents();

        $this->assertSame('ID: 123', $body);
    }

    public function testAnyParameter(): void
    {
        $response = $this->mockRequest('/test');

        $body = $response->getBody()->getContents();

        $this->assertSame('Slot: test', $body);
    }

    public function testUnmatchedRoute(): void
    {
        $this->expectException(NoRouteFoundException::class);
        $this->mockRequest('/123/test');
    }

    public function testNoResponse(): void
    {
        $this->expectException(NoResponseException::class);
        $this->mockRequest('/no-response');
    }
}
