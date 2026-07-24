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
        Schema::create('procurement_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique()->index();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('restrict');
            $table->string('item_name');
            $table->string('urgency')->default('normal');
            $table->string('status')->default('draft');
            $table->string('current_owner');
            $table->text('description');
            $table->date('target_needed_date');
            $table->string('vendor_name')->nullable();
            $table->string('po_number')->nullable();
            $table->string('rack_location')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_cases');
    }
};
