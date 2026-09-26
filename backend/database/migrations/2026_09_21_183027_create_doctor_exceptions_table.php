<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_exceptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('exception_date');

            $table->boolean('is_unavailable')
                ->default(true);

            $table->time('start_time')
                ->nullable();

            $table->time('end_time')
                ->nullable();

            $table->string('reason')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'doctor_id',
                'exception_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_exceptions');
    }
};