<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('motorcycle_id')->constrained()->cascadeOnDelete();
            $table->integer('mileage');
            $table->date('performed_at');
            $table->string('garage')->nullable();
            $table->decimal('total_amount', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('ai_extraction_status', ['pending', 'processing', 'done', 'failed'])
                ->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['motorcycle_id', 'performed_at']);
            $table->index(['motorcycle_id', 'mileage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
