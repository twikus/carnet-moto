<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('motorcycle_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('interval_km')->nullable();
            $table->integer('interval_days')->nullable();
            $table->integer('alert_threshold_km')->nullable();
            $table->integer('alert_threshold_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_types');
    }
};
