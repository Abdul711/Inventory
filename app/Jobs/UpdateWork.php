<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ApplicantDocument;
use App\Models\Applicant;
use App\Models\ApplicantWork;


class UpdateWork implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $work_id;
    public function __construct($work_id)

    {
        //
        $this->work_id=$work_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
          $applicantDocuments = [];
          $candidate_exp=ApplicantWork::where("id",$this->work_id)->first();
          $applicant=Applicant::where("id",$candidate_exp->applicant_id)->first();
              $from = Carbon::parse($candidate_exp['start_date']);
                    $to = Carbon::parse($candidate_exp['end_date']);
                    $difference = $from->diff($to);
                    $year = $difference->y;
                    $month_of_experience = $year * 12;
                    $experience = "{$difference->y} years, {$difference->m} months";
             $pdfexperienceletter = Pdf::loadView('pdf.experience_letter', [
                        'candidate' => $applicant->full_name,
                        'gender' =>  $applicant->gender,
                        'date_of_birth' =>$applicant->date_of_birth,
                        'company_name' => $candidate_exp['company'],
                        'from' => $from,
                    'photo' => $applicant->photo,          
                        'to' => $to,
                        'cnic' => $applicant->cnic,
                        'designation' => $candidate_exp['designation'],
                        'experience' => $experience,
                        'father_name' =>  $applicant->father_name,
                        'applicannt_photo' =>$applicant->photo,
                    ]);

                    $letter_name = 'experience_letter-' . str()->slug( $applicant->full_name) . time() . $candidate_exp['company'] . '.pdf';
                    $pathexperience = 'documents/' . str()->slug($applicant->full_name) .'/' . $letter_name;
                    Storage::disk('public')->put($pathexperience, $pdfexperienceletter->output());
                    $fullPath = Storage::disk('public')->path($pathexperience);
                    $mimeType = mime_content_type($fullPath);
                    $size = filesize($fullPath);
                       $applicantDocuments[] = [
                        'applicant_id' => $applicant->id,
                        'document_type' => 'experience_letter',
                        'file_name' =>$candidate_exp['company']. ' Experience Letter',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $pathexperience,
                        'mime_type' => $mimeType,
     
     
                        ];


                             foreach($applicantDocuments as $applicantDocument){
              
    ApplicantDocument::updateOrCreate(
        [
            'applicant_id'  => $applicantDocument['applicant_id'],
            'document_type' => $applicantDocument['document_type'],
            'file_name'     => $applicantDocument['file_name'], 
          
        ],
        [
           
            'file_size' => $applicantDocument['file_size'],
            'file_path' => $applicantDocument['file_path'],
            'mime_type' => $applicantDocument['mime_type'],
        ]
    );
                             }

        
        
    }
}