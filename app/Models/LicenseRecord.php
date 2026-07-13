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
     * @return BelongsTo<ImportBatch, $this>
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
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
