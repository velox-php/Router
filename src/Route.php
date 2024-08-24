<?php

namespace Velox\Router;

use Closure;

readonly class Route
{
    /** @var Method[] */
    public array $methods;

    public array $steps;
    public string $regexPath;

    /**
     * @param callable[] $middleware
     * @param callable[] $postware
     * @param Method[] $methods
     */
    public function __construct(
        public string $path,
        Closure       $handler,
        array         $middleware = [],
        array         $postware = [],
        array|Method  $methods = Method::GET
    ) {
        $parts = explode('/', $path);

        foreach ($parts as &$part) {
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $part = substr($part, 1, -1);

                $paramParts = explode(':', $part, 2);

                if (count($paramParts) === 1) {
                    $paramParts[] = '[^\/]+';
                }

                $part = '(?<' . $paramParts[0] . '>' . $paramParts[1] . ')';

                continue;
            }

            $part = preg_quote($part, '/');
        }

        $this->regexPath = '/^' . implode('\/', $parts) . '$/';

        $this->steps = [...$middleware, $handler, ...$postware];
        $this->methods = is_array($methods) ?
            array_filter($methods, static fn (mixed $method) => $method instanceof Method) :
            [$methods];
    }
}