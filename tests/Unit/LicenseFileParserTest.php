<?php

namespace Tests\Unit;

use App\Services\LicenseFileParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class LicenseFileParserTest extends TestCase
{
    public function test_it_parses_tab_delimited_txt_with_windows_encoding(): void
    {
        $path = $this->temporaryPath('licenses.txt');
        $contents = implode("\r\n", [
            'License #	License prefix	Entity name	Entity type	License status	Email	Expiration date',
            '100-001	01126	Example Escarrá	Individual	Active	Person@Example.com	5/31/2029',
        ]);

        file_put_contents($path, mb_convert_encoding($contents, 'Windows-1252', 'UTF-8'));

        $rows = (new LicenseFileParser)->parse($path, 'txt');

        $this->assertCount(1, $rows);
        $this->assertSame('100-001', $rows[0]['license_number']);
        $this->assertSame('Example Escarrá', $rows[0]['entity_name']);
        $this->assertSame('Person@Example.com', $rows[0]['email']);
    }

    public function test_it_imports_every_xlsx_sheet_with_matching_headers(): void
    {
        $path = $this->temporaryPath('licenses.xlsx');
        $spreadsheet = new Spreadsheet;

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Licenses');
        $sheet->fromArray($this->header(), null, 'A1');
        $sheet->fromArray(['100-001', '01126', 'Example Person', 'Individual', 'Active', 'one@example.com', '5/31/2029'], null, 'A2');

        $notes = $spreadsheet->createSheet();
        $notes->setTitle('Notes');
        $notes->fromArray(['Numbering system'], null, 'A1');

        $moreLicenses = $spreadsheet->createSheet();
        $moreLicenses->setTitle('More Licenses');
        $moreLicenses->fromArray($this->header(), null, 'A1');
        $moreLicenses->fromArray(['100-002', '01226', 'Example Org', 'Organization', 'Active', 'two@example.com', '7/31/2029'], null, 'A2');

        (new Xlsx($spreadsheet))->save($path);

        $rows = (new LicenseFileParser)->parse($path, 'xlsx');

        $this->assertCount(2, $rows);
        $this->assertSame(['100-001', '100-002'], array_column($rows, 'license_number'));
    }

    /**
     * @return array<int, string>
     */
    private function header(): array
    {
        return ['License #', 'License prefix', 'Entity name', 'Entity type', 'License status', 'Email', 'Expiration date'];
    }

    private function temporaryPath(string $filename): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('license_parser_', true).'_'.$filename;
    }
}
