<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interview;
use Spatie\Browsershot\Browsershot;
class InterviewEntryPassController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request,Interview $interview)
    {
         $interview->load([
            'applicant',
            'interviewer',
            'jobApplication.jobPosting',
        ]);
          $html = view('interviews.entry-pass', [
            'interview' => $interview,
        ])->render();

    $path = storage_path(
        'app/entry-pass-' . $interview->id . '.png'
    );

      
    Browsershot::html($html)
        ->timeout(120)
        ->windowSize(1400, 900)
        ->save($path);

    return response()->download(
        $path,
        'entry-pass-' . $interview->id . '.png'
    )->deleteFileAfterSend(true);
        dd($interview);
    }
}