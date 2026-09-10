<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\CandidateDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ApplicantDocument;
use App\Models\Applicant;
class UpdateImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $applicant_id;
    public function __construct($applicant_id)
    {
       $this->applicant_id=$applicant_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //\
        $applicantDocuments=[];
        $applicant=Applicant::where('id', $this->applicant_id)->first();
       $application= $applicant->jobApplications->last();
   $name = str()->slug($application->applicant->full_name);
                  $html = view('candidate.card', [
                    'name' =>$application->applicant->full_name,
                    'father_name' =>$application->applicant->father_name,
                    'gender' =>$application->applicant->gender,
                   'date_of_birth' =>$application->applicant->date_of_birth,
                    'expiry_date' => Carbon::today()->addYears(5)->format('Y-m-d'),
                    'issue_date' => Carbon::today()->format('Y-m-d'),
                    'address' =>$application->applicant->address,
                    'applicannt_photo' => $application->applicant->photo,
                    'cnic' => $application->applicant->cnic,
                ])->render();


  $relativePath = 'documents/' . str()->slug($application->applicant->full_name) ."/applicationno".$application->id . "/candidate-cnic-{$name}.png";

      Browsershot::html($html)
                    ->windowSize(1000, 1000)
                    ->deviceScaleFactor(2)
                    ->save(Storage::disk('public')->path($relativePath));
                $size = Storage::disk('public')->size($relativePath);
                $mimeType = Storage::disk('public')->mimeType($relativePath);


                
                $this->documents[] = [
                    'job_application_id' => $application->id,
                    'document_type' => 'national_id',
                    'file_name' => 'CNIC',
                    'file_size' => $size,
                    'file_path' => 'storage/' . $relativePath,
                    'mime_type' => $mimeType,
                ];
                      $applicantDocuments[] = [
                    'applicant_id' => $application->applicant->id,
                    'document_type' => 'national_id',
                    'file_name' => 'CNIC',
                    'file_size' => $size,
                    'file_path' => 'storage/' . $relativePath,
                    'mime_type' => $mimeType,
                ];
                          
                               

                      $data = [
                    'candidate' =>$application->applicant->full_name,
                    'email' => $application->applicant->email,
                    'phone' => $application->applicant->phone,
                    'linkedin' =>$application->applicant->linkedin,
                    'photo' => $application->applicant->photo,
                    'experiences' => $application->works,
                    'educations' => $application->educations,
                    'personal_details' => [
                        'father_name' => $application->applicant->father_name,
                        'dob' => date('d F Y', strtotime($application->applicant->date_of_birth)),
                        'gender' =>$application->applicant->gender,
                        'cnic' => $application->applicant->cnic,
                        'address' =>$application->applicant->address,
                    ],
                ];

                $pdf = Pdf::loadView('pdf.resume', $data)->setPaper('a4', 'portrait');

                $fileName = 'resume-' . str()->slug($application->applicant->full_name) . time() . '.pdf';
                $path = 'documents/' . str()->slug($application->applicant->full_name) . '/'."applicationno".$application->id . '/' . $fileName;

                Storage::disk('public')->put($path, $pdf->output());

                $fullPath = Storage::disk('public')->path($path);
                $mimeType = mime_content_type($fullPath);
                $size = filesize($fullPath);
             
                      $applicantDocuments[] = [
                    'applicant_id' => $application->applicant->id,
                    'document_type' => 'resume',
                    'file_name' => 'Resume',
                    'file_size' => $size,
                    'file_path' => 'storage/' . $path,
                    'mime_type' => $mimeType,
                ];
                      foreach($applicantDocuments as $applicantDocument){
              
    ApplicantDocument::updateOrCreate(
        [
            'applicant_id'  => $applicantDocument['applicant_id'],
            'document_type' => $applicantDocument['document_type'],
          
        ],
        [
            'file_name'     => $applicantDocument['file_name'],
            'file_size' => $applicantDocument['file_size'],
            'file_path' => $applicantDocument['file_path'],
            'mime_type' => $applicantDocument['mime_type'],
        ]
    );


           }
        
    }
}