<?php

namespace Velox\Router;

use Closure;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Velox\Router\Exceptions\NoResponseException;
use Velox\Router\Exceptions\NoRouteFoundException;
use Velox\Router\Exceptions\RouteException;

class Router
{
    /** @var Route[] */
    protected array $routes = [];

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function get(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::GET);
        $this->addRoute($route);

        return $route;
    }

    public function post(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::POST);
        $this->addRoute($route);

        return $route;
    }

    public function put(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::PUT);
        $this->addRoute($route);

        return $route;
    }

    public function patch(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::PATCH);
        $this->addRoute($route);

        return $route;
    }

    public function delete(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::DELETE);
        $this->addRoute($route);

        return $route;
    }

    public function head(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::HEAD);
        $this->addRoute($route);

        return $route;
    }

    public function any(
        string $path,
        Closure $handler,
        array $middleware = [],
        array $postware = [],
    ): Route {
        $route = new Route($path, $handler, $middleware, $postware, Method::cases());
        $this->addRoute($route);

        return $route;
    }

    public function resolveRoute(
        ServerRequestInterface $request
    ): ResolvedRoute {
        foreach ($this->routes as $route) {
            if (!in_array(Method::from($request->getMethod()), $route->methods, true)) {
                continue;
            }

            if (!preg_match($route->regexPath, $request->getUri()->getPath(), $parameters)) {
                continue;
            }

            return new ResolvedRoute(
                $request,
                $route,
                $parameters
            );
        }

        throw new NoRouteFoundException("No route found that matches {$request->getUri()->getPath()}");
    }

    /**
     * @throws NoResponseException
     * @throws RouteException
     * @throws NoRouteFoundException
     */
    public function emit(
        ?ServerRequestInterface $request = null,
        ?EmitterInterface $emitter = null
    ): void {
        $request ??= ServerRequestCreator::createFromGlobals();
        $emitter ??= new SapiEmitter();

        $resolved = $this->resolveRoute($request);

        $response = $resolved->run();

        $emitter->emit($response);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}