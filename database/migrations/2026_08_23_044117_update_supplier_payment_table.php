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
        Schema::table('supplier_payments', function (Blueprint $table) {
            //

            $table->foreignId('supplier_bank_account_id')
    ->nullable()
    ->constrained('supplier_bank_accounts')
    ->cascadeOnUpdate()
    ->nullOnDelete();
    $table->unique(["supplier_bank_account_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            //
        });
    }
};