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
        Schema::create('delivery_boy_bank_accounts', function (Blueprint $table) {
            $table->id();
          $table->foreignId('bank_account_id')
                ->nullable()
                
                ->constrained('bank_accounts')
                ->cascadeOnUpdate()
                ->nullOnDelete();
               $table->foreignId('delivery_boy_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_boy_bank_accounts');
    }
};