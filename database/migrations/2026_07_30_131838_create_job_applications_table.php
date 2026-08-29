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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

    $table->foreignId('job_posting_id')
        ->constrained()
        ->cascadeOnDelete();
    $table->string('month_of_exprience');
            $table->decimal("current_salary");
            $table->string("current_company");

    $table->string('last_education');
    $table->date('available_from')->nullable();
    $table->decimal('expected_salary',10,2)->nullable();
    $table->enum('status',[
        'pending',
        'shortlisted',
        'interview',
        'rejected',
        'offer',
        'screen',
        'saved',
        'hired'
    ])->default('pending');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};