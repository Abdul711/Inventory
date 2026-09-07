<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    //
      protected $guarded=[];
      public function jobApplication()
{
    return $this->belongsTo(JobApplication::class);
}

public function screenedBy()
{
    return $this->belongsTo(User::class, 'screened_by');
}
}