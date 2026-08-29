<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateWork extends Model
{
    //
      protected $guarded=[];
      public $table="candidate_work_experiences";
        protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }
}