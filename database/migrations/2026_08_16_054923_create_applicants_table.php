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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
                $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('password');
            $table->string('father_name');
            $table->text('photo');
            $table->string('cnic');
            $table->text("address");
            $table->enum('gender',["male","female"])->default("female");
            $table->string("linkedin")->nullable();
            $table->enum("martial_status",["single",'married',"divorced"])->default('single');
         $table->text("bio")->nullable();
$table->unique(['email', 'cnic']);
    $table->decimal("previous_salary")->default(0);
            $table->timestamps();
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('applicant_id')
            ->constrained('applicants')
            ->cascadeOnDelete();
        });
     
        Schema::table('interviews', function (Blueprint $table) {
            $table->foreignId('applicant_id')
            ->constrained('applicants')
            ->cascadeOnDelete();
             $table->foreignId('interviewer_id')
            ->constrained('users')
           ->cascadeOnDelete();

     


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};