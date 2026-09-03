<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    //
      protected $guarded=[];
protected $casts = [
    'terms_conditions' => 'array',
        'working_days' => 'array',
    'benefits' => 'array',
    'offer_date'=>"date",
    "expiry_date"=>"date",
    "contract_start_date"=>"date"
];
   protected static function booted()
    {
        static::creating(function ($jobOffer) {

            if (empty($jobOffer->offer_number)) {

                $year = now()->year;

                $lastOffer = JobOffer::whereYear('created_at', $year)
                    ->latest('id')
                    ->first();

                $nextNumber = $lastOffer
                    ? ((int) substr($lastOffer->offer_number, -6)) + 1
                    : 1;

                $jobOffer->offer_number = sprintf(
                    'JOB-%d-%06d',
                    $year,
                    $nextNumber
                );
            }
        });
    }


public function applicant()
{
    return $this->belongsTo(Applicant::class);
}

public function jobApplication()
{
    return $this->belongsTo(JobApplication::class);
}
      
    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}