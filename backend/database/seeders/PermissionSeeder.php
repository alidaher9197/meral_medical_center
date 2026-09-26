<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
    ['name' => 'user.view', 'description' => 'View users'],
    ['name' => 'user.create', 'description' => 'Create users'],
    ['name' => 'user.update', 'description' => 'Update users'],
    ['name' => 'user.delete', 'description' => 'Delete users'],
    

    ['name' => 'role.view', 'description' => 'View roles'],
    ['name' => 'role.create', 'description' => 'Create roles'],
    ['name' => 'role.update', 'description' => 'Update roles'],
    ['name' => 'role.delete', 'description' => 'Delete roles'],

    ['name' => 'permission.view', 'description' => 'View permissions'],
    ['name' => 'permission.create', 'description' => 'Create permissions'],
    ['name' => 'permission.update', 'description' => 'Update permissions'],
    ['name' => 'permission.delete', 'description' => 'Delete permissions'],

    ['name' => 'doctor.view', 'description' => 'View doctors'],
    ['name' => 'doctor.create', 'description' => 'Create doctors'],
    ['name' => 'doctor.update', 'description' => 'Update doctors'],
    ['name' => 'doctor.delete', 'description' => 'Delete doctors'],

    ['name' => 'patient.view', 'description' => 'View patients'],
    ['name' => 'patient.create', 'description' => 'Create patients'],
    ['name' => 'patient.update', 'description' => 'Update patients'],
    ['name' => 'patient.delete', 'description' => 'Delete patients'],
];

        foreach ($permissions as $permission) {
            \App\Models\Permission::create($permission);
        }
    }
}
