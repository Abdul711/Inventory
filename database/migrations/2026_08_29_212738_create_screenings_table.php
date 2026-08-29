<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();
              $table->foreignId('job_application_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('screened_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->enum('status', [
        'pending',
        'in_review',
        'passed',
        'failed',
        'hold'
    ])->default('pending');


        $table->unsignedTinyInteger('cv_score')->nullable();
    $table->unsignedTinyInteger('experience_score')->nullable();
    $table->unsignedTinyInteger('education_score')->nullable();

                 $table->unsignedTinyInteger('overall_score')->nullable();

    $table->text('strengths')->nullable();
    $table->text('weaknesses')->nullable();
    $table->text('remarks')->nullable();

    $table->timestamp('screened_at')->nullable();



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};