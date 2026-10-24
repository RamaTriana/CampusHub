<?php

declare(strict_types=1);

namespace CampusHub\Core;

use InvalidArgumentException;

final class Request
{
    private string $method;
    private string $path;

    public function __construct(
        string $method,
        string $uri,
        private array $query = [],
        private array $post = [],
        private array $session = [],
        private array $files = []
    ) {
        $this->method = strtoupper($method);
        $this->path = self::normalizePath($uri);
    }

    public static function fromGlobals(): self
    {
        return new self(
            (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            (string) ($_SERVER['REQUEST_URI'] ?? '/'),
            $_GET,
            $_POST,
            $_SESSION ?? [],
            $_FILES
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function session(string $key, mixed $default = null): mixed
    {
        return $this->session[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    private static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '' || $path[0] !== '/') {
            throw new InvalidArgumentException('Invalid request path.');
        }

        if (preg_match('/%(?![a-fA-F0-9]{2})|%(?:2f|5c|00)/i', $path) === 1) {
            throw new InvalidArgumentException('Invalid request path encoding.');
        }

        $path = rawurldecode($path);

        if (str_contains($path, '\\') || preg_match('/[\x00-\x1F\x7F]/', $path) === 1) {
            throw new InvalidArgumentException('Invalid request path.');
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' && $segments === []) {
                continue;
            }

            if ($segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('Invalid request path segment.');
            }

            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return $segments === [] ? '/' : '/' . implode('/', $segments);
    }
}
