<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('appointment_date');

            $table->time('appointment_time');

            $table->enum('status', [
                'pending',
                'approved',
                'cancelled',
                'completed',
                'rejected',
            ])->default('pending');

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            // Prevent double booking for the same doctor
            $table->unique([
                'doctor_id',
                'appointment_date',
                'appointment_time',
            ]);
            $table->unique([
                'patient_id',
                'appointment_date',
                'appointment_time',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};