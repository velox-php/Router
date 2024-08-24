<?php

namespace Velox\Router;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Velox\Router\Exceptions\NoResponseException;
use Velox\Router\Exceptions\RouteException;

readonly class ResolvedRoute
{
    public function __construct(
        public ServerRequest $request,
        public Route $route,
        public array $parameters
    ) {
    }

    /**
     * @throws NoResponseException
     * @throws RouteException
     */
    public function run(): ResponseInterface
    {
        $runner = (new RouteRunner($this->request, $this->route->steps, $this->parameters));

        try {
            $response = $runner();
        } catch (NoResponseException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RouteException('An error occurred while running the route', previous: $e);
        }

        return $response;
    }
}