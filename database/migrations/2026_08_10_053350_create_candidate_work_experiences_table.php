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
        Schema::create('candidate_work_experiences', function (Blueprint $table) {
            $table->id();
                $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();
                  $table->decimal("month_of_experience");
            $table->string('designation');
            $table->string('company');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('experience_type', ['full-time', 'freelance','part-time','contract','internship'])->default('full-time');
            $table->text('responsibility')->nullable();
          $table->text('benefits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_work_experiences');
    }
};