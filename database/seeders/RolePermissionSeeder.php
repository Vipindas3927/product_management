<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'create products', 'guard_name' => 'web'],
            ['name' => 'edit admin products', 'guard_name' => 'web'],
            ['name' => 'delete admin products', 'guard_name' => 'web'],
            ['name' => 'edit sub admin products', 'guard_name' => 'web'],
            ['name' => 'delete sub admin products', 'guard_name' => 'web'],
            ['name' => 'create sub admin', 'guard_name' => 'web'],
        ];
         Permission::insert( $permissions);
 
         $admin = Role::create(['name' => 'admin']);
         $subAdmin = Role::create(['name' => 'sub_admin']);
 
         $admin->givePermissionTo(Permission::all());
 
         $subAdmin->givePermissionTo(['create products', 'edit sub admin products', 'delete sub admin products']);

         User::where('user_type', 'admin')->each(fn ($user) => $user->assignRole('admin'));
         User::where('user_type', 'sub_admin')->each(fn ($user) => $user->assignRole('sub_admin'));
    }
}
