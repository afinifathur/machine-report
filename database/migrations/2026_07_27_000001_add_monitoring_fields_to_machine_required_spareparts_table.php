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
        Schema::table('machine_required_spareparts', function (Blueprint $table) {
            $table->integer('qty_per_machine')->default(1)->after('warehouse_item_code');
            $table->integer('lead_time_days')->default(7)->after('qty_per_machine');
            $table->string('maintenance_criticality', 1)->default('C')->after('lead_time_days');
            $table->text('notes')->nullable()->after('maintenance_criticality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_required_spareparts', function (Blueprint $table) {
            $table->dropColumn(['qty_per_machine', 'lead_time_days', 'maintenance_criticality', 'notes']);
        });
    }
};
