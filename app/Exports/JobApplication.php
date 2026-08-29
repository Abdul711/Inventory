<?php

namespace App\Exports;

use App\Models\JobApplication as Application;
use Maatwebsite\Excel\Concerns\FromCollection;

class JobApplication implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Application::get()->map(function($application){
              return[
                "id"=>$application->id,
              ];
        });
    }
}