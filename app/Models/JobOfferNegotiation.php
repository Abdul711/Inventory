<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOfferNegotiation extends Model
{
    //
      protected $guarded=[];
          public function jobOffer()
    {
        return $this->belongsTo(
            JobOffer::class,
            'job_offer_id'
        );
    }

    public function applicant()
    {
        return $this->belongsTo(
            Applicant::class,
            'applicant_id'
        );
    }

   
}