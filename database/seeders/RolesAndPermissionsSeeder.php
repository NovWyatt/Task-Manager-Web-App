<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Xóa cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo permissions
        // Project permissions
        Permission::firstOrCreate(['name' => 'view projects']);
        Permission::firstOrCreate(['name' => 'create projects']);
        Permission::firstOrCreate(['name' => 'edit projects']);
        Permission::firstOrCreate(['name' => 'delete projects']);
        Permission::firstOrCreate(['name' => 'manage project members']);

        // Task permissions
        Permission::firstOrCreate(['name' => 'view tasks']);
        Permission::firstOrCreate(['name' => 'create tasks']);
        Permission::firstOrCreate(['name' => 'edit tasks']);
        Permission::firstOrCreate(['name' => 'delete tasks']);
        Permission::firstOrCreate(['name' => 'assign tasks']);
        Permission::firstOrCreate(['name' => 'change task status']);

        // Category permissions
        Permission::firstOrCreate(['name' => 'manage categories']);

        // Tạo roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Gán permissions cho roles
        $adminRole->givePermissionTo(Permission::all());

        $managerRole->givePermissionTo([
            'view projects', 'create projects', 'edit projects',
            'manage project members',
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            'assign tasks', 'change task status',
            'manage categories'
        ]);

        $memberRole->givePermissionTo([
            'view projects',
            'view tasks', 'create tasks', 'edit tasks',
            'change task status'
        ]);

        // Tạo user admin mặc định nếu chưa tồn tại
        $user = User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }
        
        // Sử dụng syncRoles thay vì assignRole để tránh lỗi
        $user->syncRoles('admin');
        
        $this->command->info('Admin user created with email admin@example.com');
    }
}