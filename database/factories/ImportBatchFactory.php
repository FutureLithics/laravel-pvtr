<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_filename' => fake()->word().'.xlsx',
            'stored_path' => 'imports/'.fake()->uuid().'.xlsx',
            'file_type' => 'xlsx',
            'status' => ImportStatus::Completed,
            'total_rows' => 0,
            'imported_rows' => 0,
            'skipped_rows' => 0,
            'error_message' => null,
        ];
    }
}
