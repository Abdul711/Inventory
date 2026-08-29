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
        Schema::create('job_failures', function (Blueprint $table) {
            $table->id();
               $table->foreignId('job_log_id')
        ->constrained('job_logs')
        ->cascadeOnDelete();

    $table->string('failure_type');

    // Short explanation
    $table->string('reason');

    // Human-readable explanation
    $table->text('human_message');

    // Technical exception
    $table->longText('technical_error')->nullable();

    // What administrator should do
    $table->text('action_required')->nullable();

    // Additional information
    $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_failures');
    }
};