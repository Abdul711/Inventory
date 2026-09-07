<?php

use Livewire\Component;
use App\Models\Screening;

new class extends Component {
    public int $id;

    public array $screening = [];
    public array $applicant = [];
    public array $job = [];

    public function mount($id): void
    {
        $this->id = (int) $id;

        $screeningData = Screening::with(['jobApplication.applicant', 'jobApplication.jobPosting.designation', 'jobApplication.jobPosting.department', 'screenedBy'])->findOrFail($this->id);

        $application = $screeningData->jobApplication;
        $applicant = $application?->applicant;
        $jobPosting = $application?->jobPosting;

        $overallScore = $screeningData->overall_score;

        if ($overallScore === null) {
            $overallScore = round(($screeningData->cv_score + $screeningData->experience_score + $screeningData->education_score) / 3, 2);
        }

        $this->screening = [
            'id' => $screeningData->id,

            'job_application_id' => $screeningData->job_application_id,

            'screened_by' => $screeningData->screenedBy?->name ?? 'N/A',

            'cv_score' => $screeningData->cv_score,
            'experience_score' => $screeningData->experience_score,
            'education_score' => $screeningData->education_score,

            'overall_score' => $overallScore,

            'status' => $screeningData->status,

            'strengths' => $screeningData->strengths ?: 'No strengths added.',

            'weaknesses' => $screeningData->weaknesses ?: 'No weaknesses added.',

            'remarks' => $screeningData->remarks ?: 'No remarks added.',

            'screened_at' => $screeningData->screened_at ? \Carbon\Carbon::parse($screeningData->screened_at)->format('d M Y') : 'N/A',

            'created_at' => $screeningData->created_at?->format('d M Y h:i A'),
        ];

        $this->applicant = [
            'id' => $applicant?->id,

            'full_name' => $applicant?->full_name ?? 'N/A',

            'email' => $applicant?->email ?? 'N/A',

            'phone' => $applicant?->phone ?? 'N/A',

            'father_name' => $applicant?->father_name ?? 'N/A',

            'photo' => $applicant?->photo,
        ];

        $this->job = [
            'application_id' => $application?->id,

            'designation' => $jobPosting?->designation?->name ?? 'N/A',

            'department' => $jobPosting?->department?->name ?? 'N/A',

            'last_education' => $application?->last_education ?? 'N/A',

            'last_institute' => $application?->last_institute ?? 'N/A',

            'experience' => $application?->month_of_experience ?? 0,

            'current_company' => $application?->current_company ?? 'N/A',

            'current_salary' => $application?->current_salary ?? 0,

            'expected_salary' => $application?->expected_salary ?? 0,

            'available_from' => $application?->available_from ? \Carbon\Carbon::parse($application->available_from)->format('d M Y') : 'N/A',

            'status' => $application?->status ?? 'N/A',
        ];
    }
};

?>

<div>

    {{-- Header --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Screening Details
            </h3>

            <p class="text-muted mb-0">
                Applicant screening information and evaluation
            </p>

        </div>

        <div>

            <a href="{{ route('jobs.screenings.edit', $screening['id']) }}" class="btn btn-primary rounded-pill">
                Edit Screening
            </a>

            <a href="{{ route('jobs.screenings.index') }}" class="btn btn-secondary rounded-pill">
                Back
            </a>

        </div>

    </div>


    {{-- Score Cards --}}

    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    <h6>CV Score</h6>

                    <h3 class="fw-bold text-primary">
                        {{ $screening['cv_score'] }}/5
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    <h6>Experience Score</h6>

                    <h3 class="fw-bold text-info">
                        {{ $screening['experience_score'] }}/5
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    <h6>Education Score</h6>

                    <h3 class="fw-bold text-warning">
                        {{ $screening['education_score'] }}/5
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    <h6>Overall Score</h6>

                    @if ($screening['overall_score'] >= 4)
                        <h3 class="fw-bold text-success">
                            {{ number_format($screening['overall_score'], 2) }}/5
                        </h3>
                    @elseif ($screening['overall_score'] >= 3)
                        <h3 class="fw-bold text-warning">
                            {{ number_format($screening['overall_score'], 2) }}/5
                        </h3>
                    @else
                        <h3 class="fw-bold text-danger">
                            {{ number_format($screening['overall_score'], 2) }}/5
                        </h3>
                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- Applicant Information --}}

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                Applicant Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <p>
                        <strong>Name:</strong>
                        {{ $applicant['full_name'] }}
                    </p>

                    <p>
                        <strong>Email:</strong>
                        {{ $applicant['email'] }}
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        {{ $applicant['phone'] }}
                    </p>

                </div>

                <div class="col-md-6">

                    <p>
                        <strong>Father Name:</strong>
                        {{ $applicant['father_name'] }}
                    </p>

                    <p>
                        <strong>Application ID:</strong>

                        #{{ $screening['job_application_id'] }}
                    </p>

                    <p>
                        <strong>Application Status:</strong>

                        <span class="badge bg-info">

                            {{ ucfirst($job['status']) }}

                        </span>

                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- Job Information --}}

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                Job Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <p>
                        <strong>Designation:</strong>
                        {{ $job['designation'] }}
                    </p>

                    <p>
                        <strong>Department:</strong>
                        {{ $job['department'] }}
                    </p>

                    <p>
                        <strong>Education:</strong>
                        {{ $job['last_education'] }}
                    </p>

                    <p>
                        <strong>Institute:</strong>
                        {{ $job['last_institute'] }}
                    </p>

                </div>


                <div class="col-md-6">

                    <p>
                        <strong>Experience:</strong>

                        {{ $job['experience'] }} Months
                    </p>

                    <p>
                        <strong>Current Company:</strong>
                        {{ $job['current_company'] }}
                    </p>

                    <p>
                        <strong>Current Salary:</strong>

                        Rs.
                        {{ number_format($job['current_salary'], 2) }}
                    </p>

                    <p>
                        <strong>Expected Salary:</strong>

                        Rs.
                        {{ number_format($job['expected_salary'], 2) }}
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- Screening Information --}}

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                Screening Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <p>

                        <strong>Screened By:</strong>

                        {{ $screening['screened_by'] }}

                    </p>

                    <p>

                        <strong>Screened At:</strong>

                        {{ $screening['screened_at'] }}

                    </p>

                </div>


                <div class="col-md-6">

                    <p>

                        <strong>Status:</strong>

                        @if ($screening['status'] === 'completed')
                            <span class="badge bg-success">
                                Completed
                            </span>
                        @elseif ($screening['status'] === 'rejected')
                            <span class="badge bg-danger">
                                Rejected
                            </span>
                        @elseif ($screening['status'] === 'shortlisted')
                            <span class="badge bg-info">
                                Shortlisted
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">

                                {{ ucfirst($screening['status']) }}

                            </span>
                        @endif

                    </p>

                    <p>

                        <strong>Created:</strong>

                        {{ $screening['created_at'] }}

                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- Evaluation --}}

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                Screening Evaluation
            </h5>

        </div>

        <div class="card-body">

            <div class="mb-4">

                <h6 class="fw-bold text-success">
                    Strengths
                </h6>

                <p class="mb-0">
                    {{ $screening['strengths'] }}
                </p>

            </div>

            <hr>


            <div class="mb-4">

                <h6 class="fw-bold text-danger">
                    Weaknesses
                </h6>

                <p class="mb-0">
                    {{ $screening['weaknesses'] }}
                </p>

            </div>

            <hr>


            <div>

                <h6 class="fw-bold">
                    Remarks
                </h6>

                <p class="mb-0">
                    {{ $screening['remarks'] }}
                </p>

            </div>

        </div>

    </div>


    {{-- Decision --}}

    <div class="card border-0 shadow">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                Screening Decision
            </h5>

        </div>

        <div class="card-body">

            @if ($screening['overall_score'] >= 4)
                <div class="alert alert-success mb-0">

                    <strong>Recommended for Shortlisting</strong>

                    <br>

                    Applicant achieved an overall screening score of

                    <strong>
                        {{ number_format($screening['overall_score'], 2) }}/5
                    </strong>.

                </div>
            @elseif ($screening['overall_score'] >= 3)
                <div class="alert alert-warning mb-0">

                    <strong>Requires Review</strong>

                    <br>

                    Applicant achieved an overall screening score of

                    <strong>
                        {{ number_format($screening['overall_score'], 2) }}/5
                    </strong>.

                </div>
            @else
                <div class="alert alert-danger mb-0">

                    <strong>Not Recommended</strong>

                    <br>

                    Applicant achieved an overall screening score of

                    <strong>
                        {{ number_format($screening['overall_score'], 2) }}/5
                    </strong>.

                </div>
            @endif

        </div>

    </div>

</div>
