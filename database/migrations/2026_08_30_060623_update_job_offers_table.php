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
        Schema::table('job_offers', function (Blueprint $table) {
            //
              $table->string('offer_number')->unique()->after('id');
  $table->foreignId('applicant_id')
            ->constrained('applicants')
            ->cascadeOnDelete();
              $table->date('offer_date')->after('applicant_id');
   
              $table->date('expiry_date')->nullable()->after('offer_date');
               $table->decimal('duty_durations')->nullable()->after('expiry_date');
                   $table->string('offer_letter_path')->nullable()->after('duty_durations');

    $table->timestamp('accepted_at')->nullable()->after('offer_letter_path');

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()
        ->after("accepted_at");

    $table->string('working_days')->nullable();
        $table->string('contact_person')->nullable();
          $table->string('contact_email')->nullable();
           $table->json('benefits')->nullable();
                 $table->text('additional_notes')->nullable();
    $table->timestamp('approved_at')->nullable()->after("accepted_at");
    $table->foreignId('approved_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete()->after('approved_at');
           $table->decimal('candidate_expected_salary', 12, 2)
        ->nullable()
        ->after('job_application_id');

    $table->text('candidate_negotiation_note')
        ->nullable()
        ->after('candidate_expected_salary');

    $table->timestamp('negotiated_at')
        ->nullable()
        ->after('candidate_negotiation_note');
  
    // Company cancels/withdraws an already issued offer
    $table->timestamp('withdrawn_at')->nullable()->after('approved_at');
  $table->decimal('approved_salary', 12, 2)
        ->nullable()
        ->after('job_application_id');
            $table->json('terms_conditions')->nullable()->after('candidate_negotiation_note');
    $table->text('withdrawal_reason')->nullable()->after("withdrawn_at");  
       $table->date('contract_start_date')->nullable()->after("withdrawal_reason");  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            //
        });
    }
};