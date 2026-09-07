<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedJob extends Model
{
    //
      protected $guarded=[];
public function jobPosting()
{
    return $this->belongsTo(JobPosting::class, 'job_posting_id');
}

public function applicant()
{
    return $this->belongsTo(Applicant::class, 'applicant_id');
}

    
}