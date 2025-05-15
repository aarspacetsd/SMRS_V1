<?php
namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Grimthorr\LaravelSpatial\Point;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Cek dan buat role jika belum ada
        $adminRole = Role::findOrCreate('admin', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        // Cek dan buat permissions jika belum ada
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $managePostsPermission = Permission::firstOrCreate(['name' => 'manage posts', 'guard_name' => 'web']);

        // Assign permissions to roles (pastikan tidak duplikat)
        $adminRole->givePermissionTo([$manageUsersPermission, $managePostsPermission]);
        $userRole->givePermissionTo($managePostsPermission);

        // Membuat user admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),

            ]
        );
        $admin->assignRole($adminRole);

        // Membuat user biasa
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password123'),

            ]
        );
        $user->assignRole($userRole);
    }
}
