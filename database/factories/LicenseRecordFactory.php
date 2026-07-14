<?php

namespace Database\Factories;

use App\Models\LicenseRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseRecord>
 */
class LicenseRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'import_batch_id' => null,
            'license_number' => fake()->unique()->numerify('###-###'),
            'license_prefix' => fake()->numerify('#####'),
            'entity_name' => fake()->name(),
            'entity_type' => fake()->randomElement(['Individual', 'Organization']),
            'license_status' => 'Active',
            'email' => fake()->unique()->safeEmail(),
            'expiration_date' => now()->addYear()->toDateString(),
            'is_current' => true,
            'source_row' => null,
        ];
    }

    /**
     * Indicate that the record is no longer part of the current snapshot.
     */
    public function notCurrent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => false,
        ]);
    }

    /**
     * Indicate that the record has already expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => now()->subDay()->toDateString(),
        ]);
    }
}
