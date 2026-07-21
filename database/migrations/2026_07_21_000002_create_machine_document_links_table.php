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
        Schema::dropIfExists('machine_document_links');

        Schema::create('machine_document_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_category'); // Manual, Electrical, Hydraulic, Pneumatic, PLC, Parameter, Certificate, Vendor, Other
            $table->string('library_url', 255);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Prevent linking the exact same URL twice to the same machine
            $table->unique(['machine_id', 'library_url'], 'machine_doc_links_machine_url_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_document_links');
    }
};
