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
        Schema::create('job_logs', function (Blueprint $table) {
            $table->id();
               // Laravel queue UUID
    $table->string('job_uuid')->unique();

    $table->string('job_name');

    $table->string('queue')->nullable();

    $table->enum('status', [
        'pending',
        'processing',
        'completed',
        'failed',
    ])->default('pending');

    $table->unsignedInteger('attempts')->default(0);

    $table->timestamp('started_at')->nullable();

    $table->timestamp('completed_at')->nullable();

    $table->timestamp('failed_at')->nullable();

    // Execution time in milliseconds
    $table->unsignedBigInteger('execution_time')->nullable();

    // Original queue payload
    $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_logs');
    }
};