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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
              $table->string('bank_name');
            $table->string('account_title');
            $table->string('account_number')->unique();

            $table->string('iban')->nullable();

            $table->string('branch_name')->nullable();
            $table->string('branch_code')->nullable();

            $table->string('swift_code')->nullable();

            $table->boolean('is_primary')
                ->default(true);

            $table->text('notes')
                ->nullable();
            $table->enum("account_type",["employee","supplier","deliveryboy"])->default("employee");    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};