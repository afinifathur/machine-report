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
        if (!Schema::hasTable('master_production_areas')) {
            Schema::create('master_production_areas', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('machines', 'production_area_id')) {
            Schema::table('machines', function (Blueprint $table) {
                $table->foreignId('production_area_id')->nullable()->after('production_area')->constrained('master_production_areas')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('machines', 'production_area_id')) {
            Schema::table('machines', function (Blueprint $table) {
                $table->dropForeign(['production_area_id']);
                $table->dropColumn('production_area_id');
            });
        }

        Schema::dropIfExists('master_production_areas');
    }
};
