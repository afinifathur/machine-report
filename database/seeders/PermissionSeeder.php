<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions catalog grouped by module
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Machine
            'machine.view',
            'machine.create',
            'machine.edit',
            'machine.delete',

            // Breakdown
            'breakdown.view',
            'breakdown.create',
            'breakdown.assign',
            'breakdown.execute',
            'breakdown.verify',

            // Preventive
            'preventive.view',
            'preventive.create',
            'preventive.assign',
            'preventive.execute',
            'preventive.verify',

            // Planning
            'planning.view',
            'planning.create',
            'planning.assign',
            'planning.execute',
            'planning.verify',
            'planning.print',

            // Sparepart
            'sparepart.view',
            'sparepart.manage',

            // Procurement
            'procurement.view',
            'procurement.create',
            'procurement.process',
            'procurement.receive',
            'procurement.pickup',
            'procurement.approve.stage1',
            'procurement.approve.stage2',

            // Employee
            'employee.view',
            'employee.manage',

            // Report
            'report.view',

            // Administration
            'admin.manage.users',
            'admin.manage.roles',
            'admin.manage.settings',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Define logical system and business roles and assign permissions
        $roles = [
            'System Administrator' => [
                'admin.manage.users',
                'admin.manage.roles',
                'admin.manage.settings',
                'employee.view',
                'employee.manage',
            ],
            'Director' => [
                'dashboard.view',
                'report.view',
                'procurement.view',
                'procurement.approve.stage2',
                'machine.view',
                'planning.view',
                'breakdown.view',
                'preventive.view',
                'employee.view',
                'sparepart.view',
            ],
            'Management Representative' => [
                'dashboard.view',
                'machine.view',
                'planning.view',
                'breakdown.view',
                'preventive.view',
                'procurement.view',
                'report.view',
                'sparepart.view',
                'employee.view',
            ],
            'Maintenance Manager' => [
                'dashboard.view',
                'report.view',
                'machine.view',
                'planning.view',
                'planning.create',
                'planning.assign',
                'planning.verify',
                'planning.print',
                'breakdown.view',
                'breakdown.create',
                'breakdown.assign',
                'breakdown.verify',
                'preventive.view',
                'preventive.create',
                'preventive.assign',
                'employee.view',
                'sparepart.view',
                'procurement.view',
                'procurement.create',
                'procurement.approve.stage1',
                'procurement.pickup',
            ],
            'Maintenance Administrator' => [
                'dashboard.view',
                'report.view',
                'machine.view',
                'machine.create',
                'machine.edit',
                'machine.delete',
                'planning.view',
                'planning.create',
                'planning.execute',
                'planning.print',
                'breakdown.view',
                'breakdown.create',
                'breakdown.execute',
                'preventive.view',
                'preventive.create',
                'preventive.execute',
                'employee.view',
                'sparepart.view',
                'procurement.view',
                'procurement.create',
                'procurement.pickup',
            ],
            'Maintenance Technician' => [
                'machine.view',
                'planning.execute',
                'breakdown.create',
                'breakdown.execute',
                'preventive.execute',
            ],
            'Purchasing' => [
                'machine.view',
                'sparepart.view',
                'procurement.view',
                'procurement.process',
            ],
            'Warehouse Administrator' => [
                'machine.view',
                'sparepart.view',
                'procurement.view',
                'procurement.receive',
                'planning.view',
            ],
            'Auditor' => [
                'dashboard.view',
                'report.view',
                'machine.view',
                'planning.view',
                'breakdown.view',
                'preventive.view',
                'employee.view',
                'procurement.view',
                'sparepart.view',
            ],
            'Admin Maintenance' => [
                'dashboard.view',
                'report.view',
                'machine.view',
                'machine.create',
                'machine.edit',
                'machine.delete',
                'planning.view',
                'planning.create',
                'planning.execute',
                'planning.print',
                'breakdown.view',
                'breakdown.create',
                'breakdown.execute',
                'preventive.view',
                'preventive.create',
                'preventive.execute',
                'employee.view',
                'sparepart.view',
                'procurement.view',
                'procurement.create',
                'procurement.pickup',
            ],
            'Kabag Maintenance' => [
                'dashboard.view',
                'report.view',
                'machine.view',
                'planning.view',
                'planning.create',
                'planning.assign',
                'planning.verify',
                'planning.print',
                'breakdown.view',
                'breakdown.create',
                'breakdown.assign',
                'breakdown.verify',
                'preventive.view',
                'preventive.create',
                'preventive.assign',
                'employee.view',
                'sparepart.view',
                'procurement.view',
                'procurement.create',
                'procurement.approve.stage1',
                'procurement.pickup',
            ],
            'Direktur' => [
                'dashboard.view',
                'report.view',
                'procurement.view',
                'procurement.approve.stage2',
                'machine.view',
                'planning.view',
                'breakdown.view',
                'preventive.view',
                'employee.view',
                'sparepart.view',
            ],
            'Admin Sparepart' => [
                'machine.view',
                'sparepart.view',
                'procurement.view',
                'procurement.receive',
                'planning.view',
            ],
            'MR' => [
                'dashboard.view',
                'machine.view',
                'planning.view',
                'breakdown.view',
                'preventive.view',
                'procurement.view',
                'report.view',
                'sparepart.view',
                'employee.view',
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Assign default Admin user to System Administrator role
        $admin = User::where('email', 'admin@mrm.local')->first();
        if ($admin) {
            $admin->syncRoles(['System Administrator']);
        }

        // Create/Update helper users for testing and development
        $users = [
            ['name' => 'Kabag MTC', 'email' => 'kabagmtc@peroniks.com', 'role' => 'Maintenance Manager'],
            ['name' => 'Direktur', 'email' => 'direktur@peroniks.com', 'role' => 'Director'],
            ['name' => 'Purchasing', 'email' => 'purchasing@peroniks.com', 'role' => 'Purchasing'],
            ['name' => 'Admin Sparepart', 'email' => 'adminsp@peroniks.com', 'role' => 'Warehouse Administrator'],
            ['name' => 'Auditor', 'email' => 'auditor@peroniks.com', 'role' => 'Auditor'],
            ['name' => 'MR Auditor', 'email' => 'mr@peroniks.com', 'role' => 'Management Representative'],
            ['name' => 'Admin MTC', 'email' => 'adminmtc@peroniks.com', 'role' => 'Maintenance Administrator'],
            ['name' => 'Mekanik MTC', 'email' => 'tech@peroniks.com', 'role' => 'Maintenance Technician'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password'),
                ]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}
