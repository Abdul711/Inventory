<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
      protected $guarded=[];
      public function documents()
{
    return $this->hasMany(CandidateDocument::class, 'job_application_id');
}

      public function works()
{
    return $this->hasMany(CandidateWork::class, 'job_application_id');
}
      public function educations()
{
    return $this->hasMany(CandidateEducation::class, 'job_application_id');
}
public function jobPosting()
{
    return $this->belongsTo(JobPosting::class, 'job_posting_id');
}

public function applicant()
{
    return $this->belongsTo(Applicant::class, 'applicant_id');
}

public function interview()
{
    return $this->hasOne(Interview::class);
}
public function screening(){
    return $this->hasOne(Screening::class);
}
public function offer(){

 return $this->hasOne(JobOffer::class);
}

}