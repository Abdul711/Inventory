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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
                $table->foreignId('job_application_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->decimal('salary',10,2);

    $table->date('joining_date');

    $table->unsignedTinyInteger('probation_months')->default(3);

    $table->unsignedTinyInteger('notice_period_days')->default(30);
  
    $table->enum('status',[
        'pending',
        'accepted',
        'declined'
    ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};