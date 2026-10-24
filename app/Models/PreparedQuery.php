<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;
use mysqli_result;
use RuntimeException;

final class PreparedQuery
{
    public static function execute(mysqli $connection, string $sql, array $parameters = []): mysqli_result|bool
    {
        $statement = $connection->prepare($sql);

        try {
            if ($parameters !== []) {
                $types = '';
                foreach ($parameters as $parameter) {
                    $types .= is_int($parameter) ? 'i' : (is_float($parameter) ? 'd' : 's');
                }

                $arguments = [$types];
                foreach ($parameters as $index => &$parameter) {
                    $arguments[] = &$parameter;
                }
                unset($parameter);
                call_user_func_array([$statement, 'bind_param'], $arguments);
            }

            $statement->execute();

            if ($statement->field_count === 0) {
                return true;
            }

            $result = $statement->get_result();
            if (!$result instanceof mysqli_result) {
                throw new RuntimeException('Prepared query did not return a result set.');
            }

            return $result;
        } finally {
            $statement->close();
        }
    }
}
