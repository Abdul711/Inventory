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
    public function handle(): void
{
    $applicant = Applicant::with(['works', 'educations'])
        ->findOrFail($this->applicant_id);

    $applicantDocuments = [];

    $name = Str::slug($applicant->full_name);
    $folder = "documents/{$name}";

    // Important: Browsershot does not create this folder itself.
    File::ensureDirectoryExists(Storage::disk('public')->path($folder));

    /*
    |--------------------------------------------------------------------------
    | Generate CNIC image
    |--------------------------------------------------------------------------
    */

    $html = view('candidate.card', [
        'name' => $applicant->full_name,
        'father_name' => $applicant->father_name,
        'gender' => $applicant->gender,
        'date_of_birth' => $applicant->date_of_birth,
        'expiry_date' => Carbon::today()->addYears(5)->format('Y-m-d'),
        'issue_date' => Carbon::today()->format('Y-m-d'),
        'address' => $applicant->address,
        'applicannt_photo' => $applicant->photo,
        'cnic' => $applicant->cnic,
    ])->render();

    $cnicFileName = "candidate-cnic-{$name}.png";
    $cnicPath = "{$folder}/{$cnicFileName}";
    $cnicFullPath = Storage::disk('public')->path($cnicPath);

    Browsershot::html($html)
        ->windowSize(1000, 1000)
        ->deviceScaleFactor(2)
        ->save($cnicFullPath);

    $applicantDocuments[] = [
        'applicant_id' => $applicant->id,
        'document_type' => 'national_id',
        'file_name' => 'CNIC',
        'file_size' => Storage::disk('public')->size($cnicPath),
        'file_path' => 'storage/' . $cnicPath,
        'mime_type' => Storage::disk('public')->mimeType($cnicPath),
    ];
     if(count($applicant->works) > 0 ){
       


       
        foreach ($applicant->works as $key => $candidate_exp) {
            # code...
        
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
                        'file_name' => 'Experience Letter',
                        'file_size' => $size,
                        'file_path' => 'storage/' . $pathexperience,
                        'mime_type' => $mimeType,
     
     
                        ];

                    

        }
     }
    /*
    |--------------------------------------------------------------------------
    | Generate Resume PDF
    |--------------------------------------------------------------------------
    */

    $data = [
        'candidate' => $applicant->full_name,
        'email' => $applicant->email,
        'phone' => $applicant->phone,
        'linkedin' => $applicant->linkedin,
        'photo' => $applicant->photo,
        'experiences' => $applicant->works,
        'educations' => $applicant->educations,
        'personal_details' => [
            'father_name' => $applicant->father_name,
            'dob' => $applicant->date_of_birth
                ? Carbon::parse($applicant->date_of_birth)->format('d F Y')
                : null,
            'gender' => $applicant->gender,
            'cnic' => $applicant->cnic,
            'address' => $applicant->address,
        ],
    ];

    $pdf = Pdf::loadView('pdf.resume', $data)->setPaper('a4', 'portrait');

    $resumeFileName = 'resume-' . $name . '-' . now()->timestamp . '.pdf';
    $resumePath = "{$folder}/{$resumeFileName}";

    Storage::disk('public')->put($resumePath, $pdf->output());

    $applicantDocuments[] = [
        'applicant_id' => $applicant->id,
        'document_type' => 'resume',
        'file_name' => 'Resume',
        'file_size' => Storage::disk('public')->size($resumePath),
        'file_path' => 'storage/' . $resumePath,
        'mime_type' => Storage::disk('public')->mimeType($resumePath),
    ];

    /*
    |--------------------------------------------------------------------------
    | Save / update document records
    |--------------------------------------------------------------------------
    */
   
    foreach ($applicantDocuments as $document) {
        ApplicantDocument::updateOrCreate(
            [
                'applicant_id' => $document['applicant_id'],
                'document_type' => $document['document_type'],
            ],
            [
                'file_name' => $document['file_name'],
                'file_size' => $document['file_size'],
                'file_path' => $document['file_path'],
                'mime_type' => $document['mime_type'],
            ]
        );
    }
}
    /**
     * Execute the job.
     */
   
}