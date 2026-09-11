<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\ApplicantWork;
use App\Models\ApplicantEducation;
use App\Models\ApplicantDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
class ApplicantController extends Controller
{
    //\

    public function transfer(){
     

       $applicants=    Applicant::with(["jobApplications.jobPosting",'jobApplications.jobPosting.department', 'jobApplications.jobPosting.designation'])->get();
       $destinationDirectory = public_path('storage/applicant');

if (!is_dir($destinationDirectory)) {
    mkdir($destinationDirectory, 0755, true);
}  
       
       foreach($applicants as $applicant){
           $source = public_path('storage/candidate/'.$applicant->photo);
           

    

$filename = basename($source);

$destination = $destinationDirectory . '/' . $filename;

if (!file_exists($destination)) {
    copy($source, $destination);
}
$folder=public_path('storage/candidate');
      if (is_dir($folder)) {

    foreach (scandir($folder) as $item) {

        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $folder . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            // Delete subdirectory recursively
            $this->deleteDirectory($path);
        } else {
            // Delete file
            unlink($path);
        }
    }

    // Now the directory is empty
    rmdir($folder);
}


        echo"Full Name ". $applicant->full_name . "<br>";
                  foreach($applicant->jobApplications as $jobApplication ){
                         echo "Application Id ".$jobApplication->id. "<br>";    
                       echo "Designation " .$jobApplication->jobPosting->department->name ."<br>";
                     foreach($jobApplication->works as $work){
                        echo "Applicant Id".$applicantId=$applicant->id."<br>";
                       echo "Work Experinece " .  $work_experience =$work->month_of_experience;
                        echo "Designation ".  $designation=$work->designation;
                        echo "Previous Company" . $previous_company=$work->company; 
                      echo "Start Date".    $previous_employment_start_date=$work->start_date;
                         echo "End Date". $previous_employment_end_date=$work->end_date;
                         ApplicantWork::firstOrCreate([
                          "applicant_id"=>$applicant->id,
                          "month_of_experience"=>$work->month_of_experience,
                          "designation"=>	$designation,
                          "company"=> $previous_company,
                          "start_date"=>date("Y-m-d",strtotime($previous_employment_start_date)),
                          "end_date"=>date("Y-m-d",strtotime($work->end_date)),
                        ]);
                       }
                       foreach($jobApplication->educations as $education){
                
                         ApplicantEducation::firstOrCreate([
                            "applicant_id"=>$applicant->id,
                         	  "degree_name" => $education->degree_name,
                            "institute"=> $education->institute,     
                            "institute_type"=>$education->institute_type, 
                            "grade"=>$education->grade,
                           "graduate_start_year"=> date("Y-m-d",strtotime($education->graduate_start_year)),
                          "graduate_end_year"=>date("Y-m-d",strtotime($education->graduate_end_year)),
                         ]);
                       }
                  }

         }

    }
public function downloadletter($id){
       $jobOffer=\App\Models\JobOffer::find($id);
$companyLogo=public_path('images/logo.png');
$companyEmail="support@example.com";
$companyPhone=3001234567;
  $companyAddress="Korangi Crossing";
      $pdf = Pdf::loadView('pdf.jobs.offer_letter', compact('jobOffer','companyEmail','companyLogo'));
      return $pdf->download("Offer Letter".$jobOffer->offer_number.time().".pdf");
}
public function applicant(){
    set_time_limit(0);
   $applicants=Applicant::get();
   foreach($applicants as $applicant){
    
   }
   

}   
}