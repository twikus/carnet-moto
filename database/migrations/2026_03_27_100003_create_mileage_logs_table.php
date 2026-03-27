<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mileage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('motorcycle_id')->constrained()->cascadeOnDelete();
            $table->integer('mileage');
            $table->date('logged_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['motorcycle_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mileage_logs');
    }
};
