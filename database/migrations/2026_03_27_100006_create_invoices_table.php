<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_filename')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['maintenance_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
