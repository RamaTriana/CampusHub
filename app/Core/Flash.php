<?php

declare(strict_types=1);

namespace CampusHub\Core;

final class Flash
{
    public static function set(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    public static function pull(): ?array
    {
        return Session::pullFlash();
    }
}
