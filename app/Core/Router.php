<?php

declare(strict_types=1);

namespace CampusHub\Core;

use InvalidArgumentException;
use ReflectionMethod;
use Throwable;

final class Router
{
    private array $routes = [];
    private array $middlewareFactories = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function registerMiddleware(string $alias, callable $factory): void
    {
        if (preg_match('/^[a-z][a-z0-9_-]*$/', $alias) !== 1) {
            throw new InvalidArgumentException('Invalid middleware alias.');
        }

        $this->middlewareFactories[$alias] = $factory;
    }

    public function dispatch(Request $request): Response
    {
        $matchingRoutes = array_values(array_filter(
            $this->routes,
            static fn (array $route): bool => $route['path'] === $request->path()
        ));

        if ($matchingRoutes === []) {
            return Response::text('Not Found', 404);
        }

        $route = null;

        foreach ($matchingRoutes as $candidate) {
            if ($candidate['method'] === $request->method()) {
                $route = $candidate;
                break;
            }
        }

        if ($route === null) {
            $allowedMethods = array_values(array_unique(array_column($matchingRoutes, 'method')));

            return new Response(
                'Method Not Allowed',
                405,
                [
                    'Allow' => implode(', ', $allowedMethods),
                    'Content-Type' => 'text/plain; charset=UTF-8',
                ]
            );
        }

        try {
            $destination = fn (Request $currentRequest): Response => $this->invoke(
                $route['handler'],
                $currentRequest
            );

            foreach (array_reverse($route['middleware']) as $middlewareDefinition) {
                $middleware = $this->resolveMiddleware($middlewareDefinition);
                $next = $destination;

                $destination = static function (Request $currentRequest) use ($middleware, $next): Response {
                    return $middleware->handle($currentRequest, $next);
                };
            }

            return $destination($request);
        } catch (Throwable $exception) {
            error_log('CampusHub MVC request failed (' . get_class($exception) . ').');

            return Response::text('Internal Server Error', 500);
        }
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        if (!in_array($method, ['GET', 'POST'], true)) {
            throw new InvalidArgumentException('Only GET and POST routes can be registered.');
        }

        $normalizedPath = (new Request('GET', $path))->path();

        if (count($handler) !== 2 || !is_string($handler[0]) || !is_string($handler[1])) {
            throw new InvalidArgumentException('Route handlers must use [Controller::class, "action"].');
        }

        foreach ($middleware as $definition) {
            if (!is_string($definition) && (
                !is_array($definition)
                || !isset($definition[0])
                || !is_string($definition[0])
            )) {
                throw new InvalidArgumentException('Invalid route middleware definition.');
            }
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function resolveMiddleware(string|array $definition): Middleware
    {
        $alias = is_string($definition) ? $definition : $definition[0];
        $parameters = is_string($definition) ? [] : array_slice($definition, 1);

        if (!isset($this->middlewareFactories[$alias])) {
            throw new InvalidArgumentException('Middleware alias is not registered.');
        }

        $middleware = ($this->middlewareFactories[$alias])(...$parameters);

        if (!$middleware instanceof Middleware) {
            throw new InvalidArgumentException('Middleware factory must return a Middleware instance.');
        }

        return $middleware;
    }

    private function invoke(array $handler, Request $request): Response
    {
        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass) || !is_subclass_of($controllerClass, Controller::class)) {
            throw new InvalidArgumentException('Route controller is invalid.');
        }

        $method = new ReflectionMethod($controllerClass, $action);

        if (
            !$method->isPublic()
            || $method->isStatic()
            || $method->getDeclaringClass()->getName() !== $controllerClass
            || str_starts_with($action, '__')
        ) {
            throw new InvalidArgumentException('Route action is not accessible.');
        }

        $controller = new $controllerClass();
        $response = $controller->{$action}($request);

        if (!$response instanceof Response) {
            throw new InvalidArgumentException('Controller actions must return a Response.');
        }

        return $response;
    }
}
