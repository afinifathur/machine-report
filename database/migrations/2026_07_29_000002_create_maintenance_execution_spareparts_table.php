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
        Schema::create('maintenance_execution_spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('maintenance_executions')->onDelete('cascade');
            $table->string('warehouse_item_code')->index();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_execution_spareparts');
    }
};
