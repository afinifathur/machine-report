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
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->string('type')->default('pm')->after('machine_id');
            $table->string('breakdown_number')->nullable()->unique()->after('type');
            $table->timestamp('reported_at')->nullable()->after('breakdown_number');
            $table->string('reported_by')->nullable()->after('reported_at');
            $table->string('reported_department')->nullable()->after('reported_by');
            $table->timestamp('completed_at')->nullable()->after('reported_department');
            $table->integer('downtime_duration')->nullable()->after('completed_at');
            
            // Alter maintenance_template_id to be nullable
            $table->foreignId('maintenance_template_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'breakdown_number',
                'reported_at',
                'reported_by',
                'reported_department',
                'completed_at',
                'downtime_duration',
            ]);

            $table->foreignId('maintenance_template_id')->nullable(false)->change();
        });
    }
};
