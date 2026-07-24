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

        // Create permissions
        $permissions = [
            'create procurement',
            'approve stage 1',
            'approve stage 2',
            'process procurement',
            'receive warehouse',
            'pickup sparepart',
            'view all procurements',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Create roles and assign existing permissions
        $roles = [
            'Admin Maintenance' => ['create procurement', 'pickup sparepart', 'view all procurements'],
            'Kabag Maintenance' => ['approve stage 1', 'view all procurements'],
            'Direktur' => ['approve stage 2', 'view all procurements'],
            'Purchasing' => ['process procurement'],
            'Admin Sparepart' => ['receive warehouse'],
            'MR' => ['view all procurements'],
            'Auditor' => ['view all procurements'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Assign Admin Maintenance and MR to default admin user for convenience
        $admin = User::where('email', 'admin@mrm.local')->first();
        if ($admin) {
            $admin->assignRole('Admin Maintenance');
            $admin->assignRole('MR');
        }

        // Create helper users for other roles to test/use
        $users = [
            ['name' => 'Kabag MTC', 'email' => 'kabagmtc@peroniks.com', 'role' => 'Kabag Maintenance'],
            ['name' => 'Direktur', 'email' => 'direktur@peroniks.com', 'role' => 'Direktur'],
            ['name' => 'Purchasing', 'email' => 'purchasing@peroniks.com', 'role' => 'Purchasing'],
            ['name' => 'Admin Sparepart', 'email' => 'adminsp@peroniks.com', 'role' => 'Admin Sparepart'],
            ['name' => 'Auditor', 'email' => 'auditor@peroniks.com', 'role' => 'Auditor'],
            ['name' => 'MR Auditor', 'email' => 'mr@peroniks.com', 'role' => 'MR'],
            ['name' => 'Admin MTC', 'email' => 'adminmtc@peroniks.com', 'role' => 'Admin Maintenance'],
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
