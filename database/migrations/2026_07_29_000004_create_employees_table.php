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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number');
            $table->integer('employee_index')->default(1);
            $table->string('employee_code')->unique();
            $table->string('full_name');
            
            $table->foreignId('department_id')->constrained('master_departments');
            $table->foreignId('position_id')->constrained('master_positions');
            
            $table->string('employment_status')->default('ACTIVE');
            $table->date('employment_start_date');
            $table->date('employment_end_date')->nullable();
            
            $table->boolean('is_assignable')->default(false);
            $table->string('primary_skill')->nullable();
            $table->string('level')->nullable();
            $table->string('phone')->nullable();
            
            $table->foreignId('linked_user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // 1. Seed standard positions idempotently
        $positions = [
            ['code' => 'DIRECTOR', 'name' => 'Direktur', 'sort_order' => 10],
            ['code' => 'KABAG', 'name' => 'Kepala Bagian', 'sort_order' => 20],
            ['code' => 'STAFF', 'name' => 'Staff', 'sort_order' => 30],
            ['code' => 'ADMIN', 'name' => 'Administrator', 'sort_order' => 40],
            ['code' => 'AUDITOR', 'name' => 'Auditor', 'sort_order' => 50],
            ['code' => 'OPERATOR', 'name' => 'Operator / Mekanik', 'sort_order' => 60],
        ];
        foreach ($positions as $pos) {
            $exists = DB::table('master_positions')->where('code', $pos['code'])->exists();
            if (!$exists) {
                DB::table('master_positions')->insert(array_merge($pos, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Fetch/Create Idempotent Fallbacks for Migrated Users to avoid unique constraints conflicts
        $deptId = DB::table('master_departments')->where('code', 'MIGRATE-FALLBACK')->value('id');
        if (!$deptId) {
            $deptId = DB::table('master_departments')->insertGetId([
                'code' => 'MIGRATE-FALLBACK',
                'name' => 'Migrated Dept',
                'sort_order' => 999,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $posId = DB::table('master_positions')->where('code', 'MIGRATE-FALLBACK')->value('id');
        if (!$posId) {
            $posId = DB::table('master_positions')->insertGetId([
                'code' => 'MIGRATE-FALLBACK',
                'name' => 'Migrated Position',
                'sort_order' => 999,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Migrate existing users to employees using the fallback configuration
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $empNumber = 'EMP-' . (1000 + $user->id);
            
            // Check if already migrated
            $empExists = DB::table('employees')->where('linked_user_id', $user->id)->exists();
            if (!$empExists) {
                DB::table('employees')->insert([
                    'employee_number' => $empNumber,
                    'employee_index' => 1,
                    'employee_code' => $empNumber,
                    'full_name' => $user->name,
                    'department_id' => $deptId,
                    'position_id' => $posId,
                    'employment_status' => 'ACTIVE',
                    'employment_start_date' => '2020-01-01',
                    'is_assignable' => true,
                    'linked_user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
