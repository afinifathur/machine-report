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
            $table->dateTime('target_completion')->nullable()->after('completed_at');
            $table->dateTime('actual_completion')->nullable()->after('target_completion');
            $table->string('delay_reason')->nullable()->after('actual_completion');
            $table->text('delay_notes')->nullable()->after('delay_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn([
                'target_completion',
                'actual_completion',
                'delay_reason',
                'delay_notes',
            ]);
        });
    }
};
