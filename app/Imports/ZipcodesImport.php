<?php

namespace App\Imports;

use App\Models\Zipcode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ZipcodesImport implements ToCollection, WithHeadingRow
{
    public int $rowsImported = 0;

    public int $rowsSkipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->normalizeRow($row instanceof Collection ? $row : Collection::wrap($row));

            $code = $this->firstNonEmpty([
                $data['zipcode'] ?? null,
                $data['zip_code'] ?? null,
                $data['postal_code'] ?? null,
                $data['code'] ?? null,
            ]);

            if ($code === null) {
                if ($this->rowHasAnyValues($data)) {
                    $this->rowsSkipped++;
                }

                continue;
            }

            $code = substr((string) $code, 0, 255);

            Zipcode::updateOrCreate(
                ['code' => $code],
                [
                    'city' => $this->nullableString($data['city'] ?? null),
                    'state' => $this->nullableString($data['state'] ?? null),
                    'area' => $this->nullableString($data['area'] ?? null),
                    'monthly_price' => $this->nullableDecimal($data['monthly_price'] ?? null),
                    'yearly_price' => $this->decimal($data['yearly_price'] ?? null),
                    'is_active' => $this->parseBool($data['is_active'] ?? null, default: true),
                ]
            );

            $this->rowsImported++;
        }
    }

    private function normalizeRow(Collection $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $k = strtolower(str_replace([' ', '-'], '_', trim($key)));

            while (str_contains($k, '__')) {
                $k = str_replace('__', '_', $k);
            }

            $normalized[$k] = $value;
        }

        return $normalized;
    }

    private function rowHasAnyValues(array $data): bool
    {
        foreach ($data as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    private function firstNonEmpty(array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if ($c === null) {
                continue;
            }
            $t = trim((string) $c);
            if ($t !== '') {
                return $t;
            }
        }

        return null;
    }

    private function nullableString(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : substr($s, 0, 255);
    }

    private function nullableDecimal(mixed $v): ?float
    {
        if ($v === null) {
            return null;
        }

        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', $s) ?? $s;

        return round((float) $normalized, 2);
    }

    private function decimal(mixed $v): float
    {
        if ($v === null) {
            return 0;
        }

        $s = trim((string) $v);
        if ($s === '') {
            return 0;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', $s) ?? $s;

        return round((float) $normalized, 2);
    }

    private function parseBool(mixed $v, bool $default): bool
    {
        if ($v === null) {
            return $default;
        }

        $s = strtolower(trim((string) $v));
        if ($s === '') {
            return $default;
        }

        return match ($s) {
            '0', 'false', 'no', 'off', 'n', 'inactive' => false,
            default => true,
        };
    }
}
