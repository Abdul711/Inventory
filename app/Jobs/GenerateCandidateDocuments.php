<?php

namespace App\Jobs;

use App\Models\JobLog;
use App\Models\JobFailure;
use App\Models\JobApplication;
use Throwable;
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
class GenerateCandidateDocuments implements ShouldQueue
{
    use Queueable;

    public $applicationId;

    public $jobUuid;
    public $documents=[];
    /**
     * Maximum number of attempts.
     */
    public $tries =4;

    /**
     * Seconds before retry.
     */
    public $backoff = 30;


    /**
     * Create a new job instance.
     */
    public function __construct($application_id)
    {

    
        $this->applicationId = $application_id;
   


        /*
         * Generate UUID only once.
         *
         * The same UUID remains when Laravel
         * retries this job.
         * 
         * 
         * 
         */
            

        $this->jobUuid = (string) Str::uuid();
    }


    /**
     * Execute the job.
     */
      public function grade($percentage, $insurance_type)
    {
        if ($insurance_type == 'university') {
            if ($percentage >= 85 && $percentage <= 100) {
                $grade = 4.0;
            } elseif ($percentage >= 80) {
                $grade = 3.67;
            } elseif ($percentage >= 75) {
                $grade = 3.33;
            } elseif ($percentage >= 70) {
                $grade = 3.0;
            } elseif ($percentage >= 65) {
                $grade = 2.67;
            } elseif ($percentage >= 61) {
                $grade = 2.33;
            } elseif ($percentage >= 58) {
                $grade = 2.0;
            } elseif ($percentage >= 55) {
                $grade = 1.67;
            } elseif ($percentage >= 50) {
                $grade = 1.0;
            } else {
                $grade = 0.0;
            }
        } else {
            $grade = $percentage;
        }
        return $grade;
    }
    public function handle(): void
    {
        $startTime = microtime(true);
        $applicantDocuments=[];
        /*
        |--------------------------------------------------------------------------
        | Create / Update Job Log
        |--------------------------------------------------------------------------
        */
        
                 $application=JobApplication::with(["educations",'works'])->find($this->applicationId );
                  foreach($application->educations as $candidateeducation ){
                    $degree = $candidateeducation['degree_name'];
                    $institute = $candidateeducation['institute'];
                    $grade = $candidateeducation['grade'];
                    $insurance_type = $candidateeducation['institute_type'];
                    $yearstart = $candidateeducation['graduate_start_year'];
                    $yearend = $candidateeducation['graduate_end_year'];
                 $pdf = Pdf::loadView('pdf.education_certificate', [
                        'candidate' => $application->applicant->full_name,
                        'gender' => $application->applicant->gender,
                        'institute' => $institute,
                        'education' => $degree,
                        'endyear' => $yearend,
                        'yearstart' => $yearstart,
                        'grade' => $grade,
                        'father_name' => $application->applicant->father_name,
                        'applicannt_photo' => $application->applicant->photo,
                        'insurance_type' => $insurance_type,
                    ]);

                    $fileName = 'education-' . str()->slug($application->applicant->full_name) . $degree .time(). '.pdf';
                    $path = 'documents/' . str()->slug($application->applicant->full_name) .'/' ."applicationno".$application->id .'/' . $fileName;
                    Storage::disk('public')->put($path, $pdf->output());
                    $fullPath = Storage::disk('public')->path($path);
                    $size = filesize($fullPath);
                    $mimeType = mime_content_type($fullPath);
                    $this->documents[] = [
                        'job_application_id' => $application->id,
                        'document_type' => 'degree',
                        'file_name' => $degree . 'Certificate',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $path,
                        'mime_type' => $mimeType,
                    ];
                      $applicantDocuments[] = [
                        'applicant_id' => $application->applicant->id,
                        'document_type' => 'degree',
                        'file_name' => $degree . 'Certificate',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $path,
                        'mime_type' => $mimeType,
                    ];

                  }
                          foreach ($application->works as $candidate_exp) {
                    $from = Carbon::parse($candidate_exp['start_date']);
                    $to = Carbon::parse($candidate_exp['end_date']);
                    $difference = $from->diff($to);
                    $year = $difference->y;
                    $month_of_experience = $year * 12;
                    $experience = "{$difference->y} years, {$difference->m} months";
                    $pdfexperienceletter = Pdf::loadView('pdf.experience_letter', [
                        'candidate' => $application->applicant->full_name,
                        'gender' =>  $application->applicant->gender,
                        'date_of_birth' =>$application->applicant->date_of_birth,
                        'company_name' => $candidate_exp['company'],
                        'from' => $from,
                    'photo' => $application->applicant->photo,          
                        'to' => $to,
                        'cnic' => $application->applicant->cnic,
                        'designation' => $candidate_exp['designation'],
                        'experience' => $experience,
                        'father_name' =>  $application->applicant->father_name,
                        'applicannt_photo' =>$application->applicant->photo,
                    ]);
                    $letter_name = 'experience_letter-' . str()->slug( $application->applicant->full_name) . time() . $candidate_exp['company'] . '.pdf';
                    $pathexperience = 'documents/' . str()->slug($application->applicant->full_name) .'/'."applicationno".$application->id . '/' . $letter_name;
                    Storage::disk('public')->put($pathexperience, $pdfexperienceletter->output());
                    $fullPath = Storage::disk('public')->path($pathexperience);
                    $mimeType = mime_content_type($fullPath);
                    $size = filesize($fullPath);
                       $this->documents[] = [
                        'job_application_id' => $application->id,
                        'document_type' => 'experience_letter',
                        'file_name' => 'Experience Letter',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $pathexperience,
                        'mime_type' => $mimeType,
     
     
                        ];
                             $applicantDocuments[] = [
                        'applicant_id' => $application->applicant->id,
                        'document_type' => 'experience_letter',
                        'file_name' => 'Experience Letter',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $pathexperience,
                        'mime_type' => $mimeType,
     
     
                        ];



                          }


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
                $this->documents[] = [
                    'job_application_id' => $application->id,
                    'document_type' => 'resume',
                    'file_name' => 'Resume',
                    'file_size' => $size,
                    'file_path' => 'storage/' . $path,
                    'mime_type' => $mimeType,
                ];
                      $applicantDocuments[] = [
                    'applicant_id' => $application->applicant->id,
                    'document_type' => 'resume',
                    'file_name' => 'Resume',
                    'file_size' => $size,
                    'file_path' => 'storage/' . $path,
                    'mime_type' => $mimeType,
                ];


             foreach ($this->documents as $document) {
                CandidateDocument::firstOrCreate([
                    'job_application_id' => $document['job_application_id'],
                    'document_type' => $document['document_type'],
                    'file_name' => $document['file_name'],
                    'file_size' => $document['file_size'],
                    'file_path' => $document['file_path'],
                    'mime_type' => $document['mime_type'],
                ]);
              
            }
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
                
        $jobLog = JobLog::updateOrCreate(
            [
                'job_uuid' => $this->jobUuid,
            ],
            [
                'job_name' => self::class,

                'queue' => $this->job?->getQueue() ?? 'default',

                'status' => 'processing',

                'attempts' => $this->attempts(),

                'started_at' => now(),

                'completed_at' => null,

                'failed_at' => null,

                'payload' => json_encode([
                    'application_id' => $this->applicationId,
                ]),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Laravel Log
        |--------------------------------------------------------------------------
        */


        Log::info(
            'GenerateCandidateDocuments started',
            [
                'job_uuid' => $this->jobUuid,

                'application_id' => $this->applicationId,

                'attempt' => $this->attempts(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Find Application
        |--------------------------------------------------------------------------
        |
        | If application does not exist,
        | findOrFail() throws an exception.
        |
        | Laravel will retry the job.
        |
        */

   


        /*
        |--------------------------------------------------------------------------
        | Generate Candidate Documents
        |--------------------------------------------------------------------------
        |
        | Put your actual PDF/document generation code here.
        |
        */

        // Example:
        //
        // $this->generateResume($application);
        //
        // $this->generateEntryPass($application);
        //
        // $this->generateExperienceLetter($application);


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        |
        | Code only reaches this point if NO exception occurred.
        |
        */



        $executionTime = round(
            microtime(true) - $startTime,
            3
        );

        

        $jobLog->update([
            'status' => 'completed',

            'attempts' => $this->attempts(),

            'completed_at' => now(),

            'failed_at' => null,

            'execution_time' => $executionTime,
        ]);




        Log::info(
            'GenerateCandidateDocuments completed successfully',
            [
                'job_uuid' => $this->jobUuid,

                'application_id' => $this->applicationId,

                'attempt' => $this->attempts(),

                'execution_time' => $executionTime,
            ]
        );
        

        /*
        |--------------------------------------------------------------------------
        | Create Job Log If Missing
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Store Failure Details
        |--------------------------------------------------------------------------
        */
    
    }
}