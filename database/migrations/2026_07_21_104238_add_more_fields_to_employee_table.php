<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum("marital_status",["married","single"])->default("single")->after("gender");
            $table->string("emergency_contact_name")->after("marital_status")->nullable();
            $table->string("emergency_contact_number")->after("emergency_contact_name")->nullable();
            $table->string("emergency_contact_relationship")->after("emergency_contact_number")->nullable();
            $table->enum("employment_type",["internship","contract","part-time","permanent"])->after("emergency_contact_relationship")->after("emergency_contact_number")->default("permanent");
            $table->string("probation_period")->nullable()->after("employment_type");
          /*
            $table->enum("shift",["morning","evening","night"])->default("morning");
*/
               
                 $table->time('reporting_time')->nullable(); 


         DB::statement("
        ALTER TABLE employees
        MODIFY COLUMN status ENUM(
            'retired',
            'terminated',
            'active',
            'suspended'
        ) NOT NULL DEFAULT 'active'
    ");
        
        
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            //
        });
    }
};