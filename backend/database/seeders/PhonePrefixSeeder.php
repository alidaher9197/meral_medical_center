<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhonePrefix;

class PhonePrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PhonePrefix::create(['prefix' => '03']);
        PhonePrefix::create(['prefix' => '70']);  
        PhonePrefix::create(['prefix' => '71']);  
        PhonePrefix::create(['prefix' => '76']);  
        PhonePrefix::create(['prefix' => '78']);  
        PhonePrefix::create(['prefix' => '79']);  
        PhonePrefix::create(['prefix' => '81']);  
    }
}
