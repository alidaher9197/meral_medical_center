<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;
class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $specialties = [
            'Cardiology',
            'Dermatology',
            'Pediatrics',
            'Neurology',
            'Psychiatry',
            'Ophthalmology',
            'Orthopedics',
            'Gynecology',
            'Obstetrics',
            'Urology',
            'Gastroenterology',
            'Endocrinology',
            'Pulmonology',
            'Nephrology',
            'Oncology',
            'General Medicine',
            'Internal Medicine',
            'Dentistry',
            'ENT',
            'Rheumatology',
        ];

        foreach ($specialties as $specialty) {
            Specialty::create([
                'name' => $specialty,
            ]);
        }
    }
}
