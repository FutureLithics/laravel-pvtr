<?php

namespace App\Services;

use App\Support\ParsedLicenseRow;
use App\Support\TextNormalizer;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LicenseFileParser
{
    /**
     * @var array<string, string>
     */
    private const HEADER_MAP = [
        'license #' => 'license_number',
        'license prefix' => 'license_prefix',
        'entity name' => 'entity_name',
        'entity type' => 'entity_type',
        'license status' => 'license_status',
        'email' => 'email',
        'expiration date' => 'expiration_date',
    ];

    /**
     * @return array<int, ParsedLicenseRow>
     */
    public function parse(string $path, ?string $extension = null): array
    {
        $extension = strtolower($extension ?: pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt', 'tsv' => $this->parseDelimitedFile($path),
            'xlsx' => $this->parseSpreadsheet($path),
            default => throw new InvalidArgumentException('Unsupported file type. Please upload an XLSX, TXT, or TSV file.'),
        };
    }

    /**
     * @return array<int, ParsedLicenseRow>
     */
    private function parseDelimitedFile(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException('The uploaded file could not be read.');
        }

        $contents = $this->normalizeEncoding($contents);
        $lines = preg_split('/\R/u', $contents) ?: [];
        $rows = array_map(
            fn (string $line): array => str_getcsv($line, "\t", '"', '\\'),
            array_values(array_filter($lines, fn (string $line): bool => trim($line) !== ''))
        );

        return $this->rowsFromMatrix($rows);
    }

    /**
     * @return array<int, ParsedLicenseRow>
     */
    private function parseSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $parsedRows = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $parsedRows = [
                ...$parsedRows,
                ...$this->rowsFromWorksheet($worksheet),
            ];
        }

        if ($parsedRows === []) {
            throw new InvalidArgumentException('No worksheet contained the expected license headers.');
        }

        return $parsedRows;
    }

    /**
     * @return array<int, ParsedLicenseRow>
     */
    private function rowsFromWorksheet(Worksheet $worksheet): array
    {
        $matrix = $worksheet->toArray(nullValue: null, calculateFormulas: true, formatData: true, returnCellRef: false);

        try {
            return $this->rowsFromMatrix($matrix, $worksheet->getTitle());
        } catch (InvalidArgumentException) {
            return [];
        }
    }

    /**
     * @param  array<int, array<int, mixed>>  $matrix
     * @return array<int, ParsedLicenseRow>
     */
    private function rowsFromMatrix(array $matrix, ?string $worksheetName = null): array
    {
        [$headerIndex, $columns] = $this->findHeader($matrix);
        $rows = [];

        foreach (array_slice($matrix, $headerIndex + 1) as $offset => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $values = [];

            foreach ($columns as $index => $field) {
                $values[$field] = TextNormalizer::string($row[$index] ?? null);
            }

            $rows[] = new ParsedLicenseRow(
                rowNumber: $headerIndex + $offset + 2,
                worksheet: $worksheetName,
                values: $values,
            );
        }

        return $rows;
    }

    /**
     * @param  array<int, array<int, mixed>>  $matrix
     * @return array{0: int, 1: array<int, string>}
     */
    private function findHeader(array $matrix): array
    {
        foreach ($matrix as $index => $row) {
            $columns = [];

            foreach ($row as $columnIndex => $heading) {
                $normalized = $this->normalizeHeader(TextNormalizer::string($heading));

                if (array_key_exists($normalized, self::HEADER_MAP)) {
                    $columns[$columnIndex] = self::HEADER_MAP[$normalized];
                }
            }

            if (count(array_unique($columns)) === count(self::HEADER_MAP)) {
                return [$index, $columns];
            }
        }

        throw new InvalidArgumentException('The uploaded file is missing the expected license headers.');
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (TextNormalizer::string($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeader(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = str_replace("\xc2\xa0", ' ', $value);

        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }

    private function normalizeEncoding(string $contents): string
    {
        if (mb_check_encoding($contents, 'UTF-8')) {
            return $contents;
        }

        return mb_convert_encoding($contents, 'UTF-8', 'Windows-1252, ISO-8859-1');
    }
}
