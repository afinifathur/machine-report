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
        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->integer('quantity_required')->nullable()->after('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->dropColumn('quantity_required');
        });
    }
};
