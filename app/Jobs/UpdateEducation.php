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
use App\Models\ApplicantEducation;
class UpdateEducation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $educationid;
    public function __construct($education_id)
    {
        //
     $this->educationid=$education_id;
   


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
  $applicantDocuments = [];
    $candidateeducation=ApplicantEducation::where("id",$this->educationid)->first();
    $applicant=Applicant::where("id",$candidateeducation->applicant_id)->first();
          $degree = $candidateeducation['degree_name'];
                    $institute = $candidateeducation['institute'];
                    $grade = $candidateeducation['grade'];
                    $insurance_type = $candidateeducation['institute_type'];
                    $yearstart = $candidateeducation['graduate_start_year'];
                    $yearend = $candidateeducation['graduate_end_year'];
                 $pdf = Pdf::loadView('pdf.education_certificate', [
                        'candidate' => $applicant->full_name,
                        'gender' => $applicant->gender,
                        'institute' => $institute,
                        'education' => $degree,
                        'endyear' => $yearend,
                        'yearstart' => $yearstart,
                        'grade' => $grade,
                        'father_name' => $applicant->father_name,
                        'applicannt_photo' => $applicant->photo,
                        'insurance_type' => $insurance_type,
                    ]);
               $fileName = 'education-' . str()->slug($applicant->full_name) . $degree .time(). '.pdf';
                    $path = 'documents/' . str()->slug($applicant->full_name) .'/'  . $fileName;
                    Storage::disk('public')->put($path, $pdf->output());
                    $fullPath = Storage::disk('public')->path($path);
                    $size = filesize($fullPath);
                    $mimeType = mime_content_type($fullPath);
                      $applicantDocuments[] = [
                        'applicant_id' => $applicant->id,
                        'document_type' => 'degree',
                        'file_name' => $degree . 'Certificate',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $path,
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