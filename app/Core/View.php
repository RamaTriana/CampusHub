<?php

declare(strict_types=1);

namespace CampusHub\Core;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class View
{
    public static function render(string $name, array $data = []): string
    {
        if (preg_match('#^[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*$#', $name) !== 1) {
            throw new InvalidArgumentException('Invalid view name.');
        }

        $file = dirname(__DIR__) . '/Views/' . $name . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('View not found.');
        }

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $file;
            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    public static function escape(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="'
            . self::escape(CsrfToken::get())
            . '">';
    }
}
