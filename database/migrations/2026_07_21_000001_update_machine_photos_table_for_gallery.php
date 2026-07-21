<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('machine_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('machine_photos', 'title')) {
                $table->string('title')->nullable()->after('machine_id');
            }
            if (!Schema::hasColumn('machine_photos', 'photo_type')) {
                $table->string('photo_type')->default('other')->after('title');
            }
            if (!Schema::hasColumn('machine_photos', 'description')) {
                $table->text('description')->nullable()->after('photo_type');
            }
            if (!Schema::hasColumn('machine_photos', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('file_path')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('machine_photos', 'taken_at')) {
                $table->timestamp('taken_at')->nullable()->after('uploaded_by');
            }
            if (!Schema::hasColumn('machine_photos', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('taken_at');
            }
        });

        // Migrate existing records: copy 'type' to 'photo_type' and populate title if null
        DB::table('machine_photos')->get()->each(function ($photo) {
            $typeMap = [
                'overall' => ['photo_type' => 'reference', 'title' => 'Keseluruhan Mesin'],
                'name_plate' => ['photo_type' => 'name_plate', 'title' => 'Name Plate Mesin'],
                'electrical_cabinet' => ['photo_type' => 'inspection', 'title' => 'Electrical Cabinet'],
                'hydraulic_unit' => ['photo_type' => 'inspection', 'title' => 'Hydraulic Unit'],
                'control_panel' => ['photo_type' => 'inspection', 'title' => 'Control Panel'],
                'before_repair' => ['photo_type' => 'breakdown', 'title' => 'Foto Sebelum Perbaikan'],
                'after_repair' => ['photo_type' => 'repair', 'title' => 'Foto Setelah Perbaikan'],
            ];

            $update = [];
            if (empty($photo->photo_type) || $photo->photo_type === 'other') {
                if (isset($typeMap[$photo->type])) {
                    $update['photo_type'] = $typeMap[$photo->type]['photo_type'];
                }
            }

            if (empty($photo->title)) {
                if (isset($typeMap[$photo->type])) {
                    $update['title'] = $typeMap[$photo->type]['title'];
                } else {
                    $update['title'] = ucfirst(str_replace('_', ' ', $photo->type ?: 'Foto Mesin'));
                }
            }

            if (!empty($update)) {
                DB::table('machine_photos')->where('id', $photo->id)->update($update);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_photos', function (Blueprint $table) {
            if (Schema::hasColumn('machine_photos', 'uploaded_by')) {
                $table->dropForeign(['uploaded_by']);
                $table->dropColumn('uploaded_by');
            }
            $columnsToDrop = array_filter(['title', 'photo_type', 'description', 'taken_at', 'sort_order'], function ($col) {
                return Schema::hasColumn('machine_photos', $col);
            });
            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};
