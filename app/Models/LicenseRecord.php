<?php

namespace App\Models;

use Database\Factories\LicenseRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'import_batch_id',
    'license_number',
    'license_prefix',
    'entity_name',
    'entity_type',
    'license_status',
    'email',
    'expiration_date',
    'is_current',
    'source_row',
])]
class LicenseRecord extends Model
{
    /** @use HasFactory<LicenseRecordFactory> */
    use HasFactory;

    /**
     * License status value that is considered valid during verification.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * @return BelongsTo<ImportBatch, $this>
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    /**
     * Normalize a license number for comparison by removing dashes and spaces.
     */
    public static function normalizeLicenseNumber(string $value): string
    {
        return preg_replace('/[\s\-]+/', '', trim($value)) ?? '';
    }

    /**
     * Determine whether this record should verify as a valid license today:
     * it must be part of the current snapshot, active, and not expired.
     */
    public function isValidForVerification(): bool
    {
        return $this->is_current
            && strtolower((string) $this->license_status) === self::STATUS_ACTIVE
            && $this->expiration_date !== null
            && $this->expiration_date->toDateString() >= now()->toDateString();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'is_current' => 'boolean',
            'source_row' => 'array',
        ];
    }
}
