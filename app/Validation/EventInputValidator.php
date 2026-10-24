<?php

declare(strict_types=1);

namespace CampusHub\Validation;

final class EventInputValidator
{
    private const STATUSES = ['Draft', 'Publish', 'Selesai'];
    private const MYSQL_SIGNED_INT_MAX = 2147483647;

    public function validate(array $input, array $references): array
    {
        $errors = [];
        $title = $this->stringValue($input['judul'] ?? null);
        $description = $input['deskripsi'] ?? null;
        $location = $this->stringValue($input['lokasi'] ?? null);
        $date = $this->stringValue($input['tanggal'] ?? null);
        $time = $this->stringValue($input['jam'] ?? null);
        $status = $this->stringValue($input['status'] ?? null);

        if ($title === '' || $this->characterLength($title) > 150) {
            $errors[] = 'Judul wajib diisi dan maksimal 150 karakter.';
        }

        if (!is_string($description) || preg_match('//u', $description) !== 1) {
            $errors[] = 'Deskripsi event tidak valid.';
            $description = '';
        } elseif (strlen($description) > 65535) {
            $errors[] = 'Deskripsi event melebihi kapasitas kolom database.';
        }

        if ($date === '' || !$this->isValidDate($date)) {
            $errors[] = 'Tanggal event tidak valid.';
        }

        if ($time === '' || !$this->isValidTime($time)) {
            $errors[] = 'Jam event tidak valid.';
        }

        if ($location === '' || $this->characterLength($location) > 150) {
            $errors[] = 'Lokasi wajib diisi dan maksimal 150 karakter.';
        }

        $quota = filter_var($input['kuota'] ?? null, FILTER_VALIDATE_INT);

        if (!is_int($quota) || $quota < 0 || $quota > self::MYSQL_SIGNED_INT_MAX) {
            $errors[] = 'Kuota harus berupa bilangan bulat antara 0 dan 2147483647.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors[] = 'Status event tidak valid.';
        }

        $organizationId = $this->referenceId(
            $input['id_organisasi'] ?? null,
            $references['organizations'] ?? [],
            'id_organisasi'
        );
        $committeeId = $this->referenceId(
            $input['id_panitia'] ?? null,
            $references['committees'] ?? [],
            'id_user'
        );
        $categoryId = $this->referenceId(
            $input['id_kategori'] ?? null,
            $references['categories'] ?? [],
            'id_kategori'
        );

        if ($organizationId === null) {
            $errors[] = 'Pilih organisasi yang tersedia.';
        }

        if ($committeeId === null) {
            $errors[] = 'Pilih panitia aktif yang tersedia.';
        }

        if ($categoryId === null) {
            $errors[] = 'Pilih kategori yang tersedia.';
        }

        if ($errors !== []) {
            return ['error' => implode("\n", $errors), 'event' => null];
        }

        return [
            'error' => null,
            'event' => [
                'id_organisasi' => $organizationId,
                'id_panitia' => $committeeId,
                'id_kategori' => $categoryId,
                'judul' => trim($title),
                'deskripsi' => $description,
                'tanggal' => $date,
                'jam' => strlen($time) === 5 ? $time . ':00' : $time,
                'lokasi' => trim($location),
                'kuota' => $quota,
                'status' => $status,
            ],
        ];
    }

    private function referenceId(mixed $value, mixed $records, string $idKey): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        if (!is_int($id) || $id < 1 || !is_array($records)) {
            return null;
        }

        foreach ($records as $record) {
            if (is_array($record) && (int) ($record[$idKey] ?? 0) === $id) {
                return $id;
            }
        }

        return null;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) && preg_match('//u', $value) === 1
            ? trim($value)
            : '';
    }

    private function characterLength(string $value): int
    {
        $length = preg_match_all('/./us', $value);

        return $length === false ? PHP_INT_MAX : $length;
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && $date->format('Y-m-d') === $value
            && $value >= '1000-01-01'
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }

    private function isValidTime(string $value): bool
    {
        foreach (['H:i', 'H:i:s'] as $format) {
            $time = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = \DateTimeImmutable::getLastErrors();

            if (
                $time !== false
                && $time->format($format) === $value
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            ) {
                return true;
            }
        }

        return false;
    }
}
