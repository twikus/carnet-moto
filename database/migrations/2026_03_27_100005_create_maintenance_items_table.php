<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('maintenance_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->decimal('amount', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('maintenance_id');
            $table->index('maintenance_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_items');
    }
};
