<?php

declare(strict_types=1);

namespace CampusHub\Core;

abstract class Controller
{
    protected function view(string $name, array $data = [], int $status = 200): Response
    {
        return Response::html(View::render($name, $data), $status);
    }
}
