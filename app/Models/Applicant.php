<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Authenticatable
{
    //
      protected $guarded=[];
      public function jobApplications()
{
    return $this->hasMany(JobApplication::class, 'applicant_id');
}
   protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
public function educations()
{
    return $this->hasMany(
        CandidateEducation::class,
        'job_application_id'
    );
}

public function works()
{
        return $this->hasMany(
        CandidateWork::class,
        'job_application_id'
    );
}
public function applicantworks(){

   return $this->hasMany(
        ApplicantWorK::class,
        'applicant_id'
    )->orderBy('created_at');
}
public function applicanteducations()
{
    return $this->hasMany(
        ApplicantEducation::class,
        'applicant_id'
    );
}
public function documents()
{
    return $this->hasMany(
        CandidateDocument::class,
        'job_application_id'
    );
}


}