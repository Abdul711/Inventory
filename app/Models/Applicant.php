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
public function interviews(){
    return $this->hasMany(Interview::class, 'applicant_id');
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
        ApplicantEducation::class,
        'applicant_id'
    );
}

public function works()
{
        return $this->hasMany(
        ApplicantWork::class,
        'applicant_id'
    );
}

public function documents()
{
    return $this->hasMany(
        ApplicantDocument::class,
        'applicant_id'
    );
}


}