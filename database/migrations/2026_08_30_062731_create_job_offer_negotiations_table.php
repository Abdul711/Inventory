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
        Schema::create('job_offer_negotiations', function (Blueprint $table) {
            $table->id();
             $table->foreignId('job_offer_id')
        ->constrained('job_offers')
        ->cascadeOnDelete();

    $table->foreignId('applicant_id')
        ->constrained('applicants')
        ->cascadeOnDelete();

    $table->enum('proposed_by', [
        'candidate',
        'company'
    ]);

    $table->decimal('proposed_salary', 12, 2);

    $table->text('remarks')->nullable();

    $table->enum('status', [
        'pending',
        'accepted',
        'rejected',
        'countered'
    ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offer_negotiations');
    }
};