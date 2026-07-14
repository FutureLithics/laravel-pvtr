<?php

namespace App\Support;

class ParsedLicenseRow
{
    /**
     * @param  array<string, string>  $values  Header-mapped field values for this row.
     */
    public function __construct(
        public readonly int $rowNumber,
        public readonly ?string $worksheet,
        public readonly array $values,
    ) {}
}
