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
        Schema::create('procurement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->foreignId('procurement_category_id')->nullable()->constrained('procurement_categories')->onDelete('restrict');
            $table->boolean('machine_down')->default(false);
            $table->text('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->dropForeign(['procurement_category_id']);
            $table->dropColumn(['procurement_category_id', 'machine_down', 'reason']);
        });

        Schema::dropIfExists('procurement_categories');
    }
};
