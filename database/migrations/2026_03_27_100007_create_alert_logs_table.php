<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('motorcycle_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('maintenance_type_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default('discord');
            $table->text('message');
            $table->timestamp('sent_at');

            $table->index(['motorcycle_id', 'maintenance_type_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_logs');
    }
};
