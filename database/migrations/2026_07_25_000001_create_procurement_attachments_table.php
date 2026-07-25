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
        Schema::create('procurement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_case_id')->constrained('procurement_cases')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_attachments');
    }
};
