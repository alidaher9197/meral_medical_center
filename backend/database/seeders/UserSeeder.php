<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Specialty;
use App\Models\AdminPermission;
use App\Models\DoctorSpecialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get Roles
        |--------------------------------------------------------------------------
        */

        $headAdminRole = Role::where('name', 'head_admin')->firstOrFail();
        $adminRole     = Role::where('name', 'admin')->firstOrFail();
        $doctorRole    = Role::where('name', 'doctor')->firstOrFail();
        $patientRole   = Role::where('name', 'patient')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | HEAD ADMIN
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            ['email' => 'alidaher9197@gmail.com'],
            [
                'name' => 'Head Administrator',
                'password' => Hash::make('11111111'),
                'role_id' => $headAdminRole->id,
                'phone' => '71000001',
                'status' => 'approved',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ADMINS
        |--------------------------------------------------------------------------
        */

        $admins = [
            [
                'name' => 'Admin Full Access',
                'email' => 'admin.full@meralmedical.com',
                'phone' => '71000002',
                'permissions' => [
                    'user.view',
                    'user.create',
                    'user.update',
                    'user.delete',

                    'doctor.view',
                    'doctor.create',
                    'doctor.update',
                    'doctor.delete',

                    'patient.view',
                    'patient.create',
                    'patient.update',
                    'patient.delete',
                ],
            ],

            [
                'name' => 'Admin Patient Manager',
                'email' => 'admin.patient@meralmedical.com',
                'phone' => '71000003',
                'permissions' => [
                    'user.view',

                    'patient.view',
                    'patient.create',
                    'patient.update',
                ],
            ],

            [
                'name' => 'Admin Doctor Manager',
                'email' => 'admin.doctor@meralmedical.com',
                'phone' => '71000004',
                'permissions' => [
                    'user.view',

                    'doctor.view',
                    'doctor.create',
                    'doctor.update',
                ],
            ],

            [
                'name' => 'Admin View Only',
                'email' => 'admin.view@meralmedical.com',
                'phone' => '71000005',
                'permissions' => [
                    'user.view',
                    'doctor.view',
                    'patient.view',
                ],
            ],
        ];

        foreach ($admins as $adminData) {

            $admin = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make('11111111'),
                    'role_id' => $adminRole->id,
                    'phone' => $adminData['phone'],
                    'status' => 'approved',
                ]
            );

            AdminPermission::where(
                'admin_id',
                $admin->id
            )->delete();

            foreach ($adminData['permissions'] as $permissionName) {

                $permission = Permission::where(
                    'name',
                    $permissionName
                )->first();

                if (!$permission) {
                    $this->command->warn(
                        "Permission '{$permissionName}' was not found."
                    );

                    continue;
                }

                AdminPermission::create([
                    'admin_id' => $admin->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DOCTORS
        |--------------------------------------------------------------------------
        */

        $doctors = [
            [
                'name' => 'Dr. John Carter',
                'email' => 'doctor.cardiology@meralmedical.com',
                'phone' => '71000010',
                'specialties' => [
                    'Cardiology',
                ],
            ],

            [
                'name' => 'Dr. Sarah Miller',
                'email' => 'doctor.dermatology@meralmedical.com',
                'phone' => '71000011',
                'specialties' => [
                    'Dermatology',
                ],
            ],

            [
                'name' => 'Dr. Michael Brown',
                'email' => 'doctor.neurology@meralmedical.com',
                'phone' => '71000012',
                'specialties' => [
                    'Neurology',
                ],
            ],

            [
                'name' => 'Dr. Emily Wilson',
                'email' => 'doctor.pediatrics@meralmedical.com',
                'phone' => '71000013',
                'specialties' => [
                    'Pediatrics',
                ],
            ],

            [
                'name' => 'Dr. David Anderson',
                'email' => 'doctor.orthopedics@meralmedical.com',
                'phone' => '71000014',
                'specialties' => [
                    'Orthopedics',
                    'Sports Medicine',
                ],
            ],

            [
                'name' => 'Dr. Lisa Thompson',
                'email' => 'doctor.internal@meralmedical.com',
                'phone' => '71000015',
                'specialties' => [
                    'Internal Medicine',
                    'Endocrinology',
                ],
            ],
        ];

        foreach ($doctors as $doctorData) {

            $doctor = User::updateOrCreate(
                ['email' => $doctorData['email']],
                [
                    'name' => $doctorData['name'],
                    'password' => Hash::make('11111111'),
                    'role_id' => $doctorRole->id,
                    'phone' => $doctorData['phone'],
                    'status' => 'approved',
                ]
            );

            DoctorSpecialty::where(
                'doctor_id',
                $doctor->id
            )->delete();

            foreach ($doctorData['specialties'] as $specialtyName) {

                $specialty = Specialty::where(
                    'name',
                    $specialtyName
                )->first();

                if (!$specialty) {

                    $this->command->warn(
                        "Specialty '{$specialtyName}' was not found."
                    );

                    continue;
                }

                DoctorSpecialty::create([
                    'doctor_id' => $doctor->id,
                    'specialty_id' => $specialty->id,
                    'license_image' => null,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PATIENTS
        |--------------------------------------------------------------------------
        */

        $patients = [
            [
                'name' => 'James Smith',
                'email' => 'patient.james@meralmedical.com',
                'phone' => '71000020',
            ],

            [
                'name' => 'Emma Johnson',
                'email' => 'patient.emma@meralmedical.com',
                'phone' => '71000021',
            ],

            [
                'name' => 'Daniel Williams',
                'email' => 'patient.daniel@meralmedical.com',
                'phone' => '71000022',
            ],

            [
                'name' => 'Olivia Jones',
                'email' => 'patient.olivia@meralmedical.com',
                'phone' => '71000023',
            ],

            [
                'name' => 'William Davis',
                'email' => 'patient.william@meralmedical.com',
                'phone' => '71000024',
            ],

            [
                'name' => 'Sophia Martinez',
                'email' => 'patient.sophia@meralmedical.com',
                'phone' => '71000025',
            ],

            [
                'name' => 'Noah Garcia',
                'email' => 'patient.noah@meralmedical.com',
                'phone' => '71000026',
            ],

            [
                'name' => 'Mia Rodriguez',
                'email' => 'patient.mia@meralmedical.com',
                'phone' => '71000027',
            ],

            [
                'name' => 'Lucas Wilson',
                'email' => 'patient.lucas@meralmedical.com',
                'phone' => '71000028',
            ],

            [
                'name' => 'Ava Anderson',
                'email' => 'patient.ava@meralmedical.com',
                'phone' => '71000029',
            ],
        ];

        foreach ($patients as $patientData) {

            User::updateOrCreate(
                ['email' => $patientData['email']],
                [
                    'name' => $patientData['name'],
                    'password' => Hash::make('11111111'),
                    'role_id' => $patientRole->id,
                    'phone' => $patientData['phone'],
                    'status' => 'approved',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Finished
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info(' Users seeded successfully!');
        $this->command->info('========================================');
        $this->command->info('Head Admin: alidaher9197@gmail.com');
        $this->command->info('Password for all users: 11111111');
        $this->command->info('========================================');
    }
}
