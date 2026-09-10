<?php

use Livewire\Component;
use App\Models\Interview;

new class extends Component {
    public int $id;

    public array $interview = [];
    public array $applicant = [];
    public array $job = [];
    public $hasFeedback = false;
    public $feedback;
    public function mount($id): void
    {
        $this->id = (int) $id;

        $interviewData = Interview::with(['interviewer', 'feedback', 'jobApplication.applicant', 'jobApplication.jobPosting.department'])->findOrFail($this->id);

        /*
        |--------------------------------------------------------------------------
        | Interview Information
        |--------------------------------------------------------------------------
        */
        $isOverallScoreNull = $interviewData->feedback === null;
        if (!$isOverallScoreNull) {
            $this->hasFeedback = true;
            $this->feedback = $interviewData->feedback;
        }

        $this->interview = [
            'id' => $interviewData->id,

            'scheduled_at' => $interviewData->scheduled_at?->format('d M Y h:i A'),

            'type' => $interviewData->type,

            'meeting_link' => $interviewData->meeting_link,

            'status' => $interviewData->status,

            'interviewer_name' => $interviewData->interviewer?->name ?? 'N/A',

            'interviewer_email' => $interviewData->interviewer?->email ?? 'N/A',
        ];

        /*
        |--------------------------------------------------------------------------
        | Applicant Information
        |--------------------------------------------------------------------------
        */

        $applicant = $interviewData->jobApplication?->applicant;

        $this->applicant = [
            'id' => $applicant?->id,

            'full_name' => $applicant?->full_name ?? 'N/A',

            'father_name' => $applicant?->father_name ?? 'N/A',

            'email' => $applicant?->email ?? 'N/A',

            'phone' => $applicant?->phone ?? 'N/A',

            'cnic' => $applicant?->cnic ?? 'N/A',

            'gender' => $applicant?->gender ?? 'N/A',

            'address' => $applicant?->address ?? 'N/A',

            'photo' => $applicant?->photo,
        ];

        /*
        |--------------------------------------------------------------------------
        | Job Information
        |--------------------------------------------------------------------------
        */

        $jobPosting = $interviewData->jobApplication?->jobPosting;

        $this->job = [
            'id' => $jobPosting?->id,

            'job_title' => $jobPosting?->designation->name ?? 'N/A',

            'department' => $jobPosting?->department?->name ?? 'N/A',

            'employment_type' => $jobPosting?->employment_type ?? 'N/A',

            'minimum_salary' => $jobPosting?->minimum_salary,

            'maximum_salary' => $jobPosting?->maximum_salary,

            'min_experience' => $jobPosting?->min_experience,
        ];
    }
};

?>
<div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Interview Details
            </h3>

            <p class="text-muted mb-0">
                Candidate interview and applied job information
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('jobs.interviews.edit', $interview['id']) }}" class="btn btn-primary rounded-pill">
                Edit
            </a>

            <a href="{{ route('jobs.interviews.index') }}" class="btn btn-secondary rounded-pill">
                Back
            </a>

        </div>

    </div>


    {{-- Summary --}}
    <div class="row g-3 mb-4">

        {{-- Interview ID --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Interview ID
                    </h6>

                    <h3 class="fw-bold text-primary">
                        #{{ $interview['id'] }}
                    </h3>

                </div>

            </div>

        </div>


        {{-- Type --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Interview Type
                    </h6>

                    @php
                        $typeClass = match ($interview['type'] ?? '') {
                            'online' => 'bg-info',
                            'physical' => 'bg-success',
                            'phone' => 'bg-secondary',
                            default => 'bg-dark',
                        };
                    @endphp

                    <span class="badge {{ $typeClass }} fs-6">

                        {{ ucfirst($interview['type'] ?? 'N/A') }}

                    </span>

                </div>

            </div>

        </div>


        {{-- Scheduled --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Scheduled At
                    </h6>

                    <div class="fw-bold">

                        {{ $interview['scheduled_at'] ?? 'N/A' }}

                    </div>

                </div>

            </div>

        </div>


        {{-- Status --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Status
                    </h6>

                    @php

                        $statusClass = match ($interview['status'] ?? '') {
                            'completed' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            'rescheduled' => 'bg-warning text-dark',
                            'pending' => 'bg-warning text-dark',
                            default => 'bg-primary',
                        };

                    @endphp

                    <span class="badge {{ $statusClass }} fs-6">

                        {{ ucfirst($interview['status'] ?? 'pending') }}

                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- Candidate Information --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                <i class="bi bi-person me-2"></i>
                Candidate Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">
                {{-- Photo --}}
                <div class="col-md-3 text-center mb-4">

                    @php
                        $photo = $applicant['photo'] ?? null;
                        $photoUrl = null;

                        if (!empty($photo)) {
                            if (\Illuminate\Support\Str::startsWith($photo, ['http://', 'https://'])) {
                                $photoUrl = $photo;
                            } elseif (\Illuminate\Support\Str::startsWith($photo, 'storage/')) {
                                $photoUrl = asset($photo);
                            } elseif (\Illuminate\Support\Str::startsWith($photo, 'applicant/')) {
                                $photoUrl = asset('storage/' . $photo);
                            } else {
                                $photoUrl = asset('storage/applicant/' . $photo);
                            }
                        }
                    @endphp

                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $applicant['full_name'] }}"
                            class="img-thumbnail rounded-4" role="button" data-bs-toggle="modal"
                            data-bs-target="#applicantPhotoModal"
                            style="width:180px; height:200px; object-fit:cover; cursor:pointer;">
                    @else
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mx-auto"
                            style="width:180px; height:200px;">

                            <i class="bi bi-person fs-1 text-muted"></i>
                        </div>
                    @endif

                </div>


                <div class="col-md-9">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <strong>
                                Full Name
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['full_name'] }}
                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Father Name
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['father_name'] }}
                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Email
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['email'] }}
                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Phone
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['phone'] }}
                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                CNIC
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['cnic'] }}
                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Gender
                            </strong>

                            <p class="text-muted mb-0">

                                {{ ucfirst($applicant['gender']) }}

                            </p>

                        </div>


                        <div class="col-12">

                            <strong>
                                Address
                            </strong>

                            <p class="text-muted mb-0">
                                {{ $applicant['address'] }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Applied Job --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                <i class="bi bi-briefcase me-2"></i>
                Applied Job
            </h5>

        </div>


        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">

                    <strong>
                        Job Title
                    </strong>

                    <p class="text-muted mb-0">
                        {{ $job['job_title'] }}
                    </p>

                </div>


                <div class="col-md-6">

                    <strong>
                        Department
                    </strong>

                    <p class="text-muted mb-0">
                        {{ $job['department'] }}
                    </p>

                </div>


                <div class="col-md-6">

                    <strong>
                        Employment Type
                    </strong>

                    <p class="text-muted mb-0">

                        {{ ucfirst($job['employment_type']) }}

                    </p>

                </div>


                <div class="col-md-6">

                    <strong>
                        Minimum Experience
                    </strong>

                    <p class="text-muted mb-0">

                        {{ $job['min_experience'] ?? 0 }}

                        Year(s)

                    </p>

                </div>


                <div class="col-md-6">

                    <strong>
                        Minimum Salary
                    </strong>

                    <p class="text-muted mb-0">

                        @if ($job['minimum_salary'])
                            Rs.
                            {{ number_format($job['minimum_salary']) }}
                        @else
                            N/A
                        @endif

                    </p>

                </div>


                <div class="col-md-6">

                    <strong>
                        Maximum Salary
                    </strong>

                    <p class="text-muted mb-0">

                        @if ($job['maximum_salary'])
                            Rs.
                            {{ number_format($job['maximum_salary']) }}
                        @else
                            N/A
                        @endif

                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- Interview Information --}}

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">

                <i class="bi bi-calendar-event me-2"></i>

                Interview Information

            </h5>

        </div>


        <div class="card-body">

            <div class="row g-4">

                {{-- Interviewer --}}
                <div class="col-md-6">

                    <strong>
                        Interviewer
                    </strong>

                    <p class="text-muted mb-0">

                        {{ $interview['interviewer_name'] }}

                    </p>

                </div>


                {{-- Email --}}
                <div class="col-md-6">

                    <strong>
                        Interviewer Email
                    </strong>

                    <p class="text-muted mb-0">

                        {{ $interview['interviewer_email'] }}

                    </p>

                </div>


                {{-- Scheduled --}}
                <div class="col-md-6">

                    <strong>
                        Scheduled At
                    </strong>

                    <p class="text-muted mb-0">

                        <i class="bi bi-calendar-event me-1"></i>

                        {{ $interview['scheduled_at'] ?? 'N/A' }}

                    </p>

                </div>


                {{-- Type --}}
                <div class="col-md-6">

                    <strong>
                        Interview Type
                    </strong>

                    <p class="mt-1 mb-0">

                        <span class="badge {{ $typeClass }}">

                            {{ ucfirst($interview['type'] ?? 'N/A') }}

                        </span>

                    </p>

                </div>


                {{-- Meeting Link --}}
                @if (($interview['type'] ?? '') === 'online' && !empty($interview['meeting_link']))
                    <div class="col-md-6">

                        <strong>
                            Meeting Link
                        </strong>

                        <p class="mt-1 mb-0">

                            <a href="{{ $interview['meeting_link'] }}" target="_blank"
                                class="btn btn-sm
                                    btn-primary
                                    rounded-pill">

                                <i
                                    class="bi
                                    bi-camera-video
                                    me-1"></i>

                                Join Interview

                            </a>

                        </p>

                    </div>
                @endif


                {{-- Status --}}
                <div class="col-md-6">

                    <strong>
                        Interview Status
                    </strong>

                    <p class="mt-1 mb-0">

                        <span class="badge
                                {{ $statusClass }}">

                            {{ ucfirst($interview['status'] ?? 'pending') }}

                        </span>

                    </p>

                </div>

            </div>

        </div>

    </div>
    @if ($this->hasFeedback)
        @php
            $recommended = $feedback->recommended == 1 ? 'Yes' : 'No';
            $typeFeedBackClass = match ($recommended ?? '') {
                'online' => 'bg-info',
                'Yes' => 'bg-success',
                'No' => 'bg-danger',
                default => 'bg-dark',
            };
        @endphp
        <div class="card border-0 shadow mb-4">

            <div class="card-header bg-light">

                <h5 class="mb-0">

                    <i class="bi bi-calendar-event me-2"></i>

                    Interview Feedback

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-4">

                    {{-- Interviewer --}}
                    <div class="col-md-6">

                        <strong>
                            Communication Score
                        </strong>

                        <p class="text-muted mb-0">
                            {{ $feedback->communication_score }}

                        </p>

                    </div>


                    {{-- Email --}}
                    <div class="col-md-6">

                        <strong>
                            Attitude Score
                        </strong>

                        <p class="text-muted mb-0">

                            {{ $feedback->attitude_score }}

                        </p>

                    </div>


                    {{-- Scheduled --}}
                    <div class="col-md-6">

                        <strong>
                            Overall Score
                        </strong>

                        <p class="text-muted mb-0">

                            {{ $feedback->overall_score }}

                        </p>

                    </div>


                    {{-- Type --}}
                    <div class="col-md-6">

                        <strong>
                            Recommended
                        </strong>

                        <p class="mt-1 mb-0">

                            <span class="badge {{ $typeFeedBackClass }}">
                                {{ $feedback->recommended == 1 ? 'Yes' : 'No' }}

                            </span>

                        </p>

                    </div>


                    {{-- Meeting Link --}}

                    <div class="col-md-6">

                        <strong>
                            Comments
                        </strong>

                        <p class="mt-1 mb-0">
                            {{ $feedback->comments }}


                        </p>

                    </div>



                    {{-- Status --}}


                </div>

            </div>

        </div>
    @endif
    {{-- Applicant Photo Preview Modal --}}
    @if ($photoUrl)
        <div class="modal fade" id="applicantPhotoModal" tabindex="-1" aria-labelledby="applicantPhotoModalLabel"
            aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="modal-title" id="applicantPhotoModalLabel">
                            {{ $applicant['full_name'] }}
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>

                    <div class="modal-body text-center p-3">
                        <img src="{{ $photoUrl }}" alt="{{ $applicant['full_name'] }}"
                            class="img-fluid rounded-3" style="max-height:75vh; object-fit:contain;">
                    </div>

                </div>
            </div>

        </div>
    @endif
</div>
