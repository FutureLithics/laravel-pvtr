<?php

namespace Tests\Feature;

use App\Models\LicenseRecord;
use App\Services\LicenseImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_import_upserts_rows_and_retires_missing_records(): void
    {
        $service = app(LicenseImportService::class);

        $service->import($this->writeTxt([
            ['100-001', '01126', 'Example Person', 'Individual', 'Active', 'one@example.com', '5/31/2029'],
            ['100-002', '01226', 'Example Org', 'Organization', 'Active', 'two@example.com', '7/31/2029'],
        ]), 'initial.txt');

        $batch = $service->import($this->writeTxt([
            ['100-002', '01226', 'Updated Org', 'Organization', 'Active', 'updated@example.com', '8/31/2029'],
        ]), 'updated.txt');

        $this->assertSame(1, $batch->imported_rows);
        $this->assertFalse(LicenseRecord::where('license_number', '100-001')->firstOrFail()->is_current);

        $updated = LicenseRecord::where('license_number', '100-002')->firstOrFail();
        $this->assertTrue($updated->is_current);
        $this->assertSame('Updated Org', $updated->entity_name);
        $this->assertSame('updated@example.com', $updated->email);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function writeTxt(array $rows): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('license_import_', true).'.txt';
        $lines = [
            implode("\t", ['License #', 'License prefix', 'Entity name', 'Entity type', 'License status', 'Email', 'Expiration date']),
        ];

        foreach ($rows as $row) {
            $lines[] = implode("\t", $row);
        }

        file_put_contents($path, implode("\n", $lines));

        return $path;
    }
}
