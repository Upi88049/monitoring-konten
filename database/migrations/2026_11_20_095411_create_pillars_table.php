<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pre-seed 5 pilar bawaan Dharma Group
        $defaultPillars = [
            'Internal/Culture',
            'Employer Branding',
            'Employee Branding',
            'Event/Job Fair',
            'Info Lowongan',
        ];

        foreach ($defaultPillars as $name) {
            DB::table('pillars')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pillars');
    }
};
