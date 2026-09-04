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
        Schema::create('account_mappings', function (Blueprint $table) {
            $table->id();
            

    $table->string('module');

    $table->string('key');

    $table->foreignId('account_id')
        ->constrained('accounts')
        ->restrictOnDelete();



            $table->timestamps();
            
    $table->unique(['module', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_mappings');
    }
};