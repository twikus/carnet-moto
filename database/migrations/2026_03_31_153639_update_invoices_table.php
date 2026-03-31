<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('maintenance_id')->nullable()->change();
            $table->string('extraction_status')->default('pending')->after('sort_order');
            $table->json('extracted_data')->nullable()->after('extraction_status');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('maintenance_id')->nullable(false)->change();
            $table->dropColumnIfExists('extraction_status');
            $table->dropColumnIfExists('extracted_data');
            $table->dropColumnIfExists('updated_at');
        });
    }
};
