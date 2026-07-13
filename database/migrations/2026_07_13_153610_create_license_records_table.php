<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('license_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('license_number')->unique();
            $table->string('license_prefix')->nullable();
            $table->string('entity_name');
            $table->string('entity_type')->nullable();
            $table->string('license_status');
            $table->string('email')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_current')->default(true);
            $table->json('source_row')->nullable();
            $table->timestamps();

            $table->index(['license_number', 'is_current']);
            $table->index(['license_prefix', 'is_current']);
            $table->index(['email', 'is_current']);
            $table->index(['entity_name', 'is_current']);
            $table->index(['license_status', 'expiration_date', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_records');
    }
};
