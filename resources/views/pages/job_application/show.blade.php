<?php

use Livewire\Component;
use App\Models\JobApplication;

new class extends Component {
    public int $id;

    public array $application = [];
    public array $educations = [];
    public array $workExperiences = [];
    public array $documents = [];
    public array $interview = [];
    public function mount($id): void
    {
        $this->id = (int) $id;

        $applicationData = JobApplication::with(['jobPosting.department', 'interview'])->findOrFail($this->id);

        $this->application = [
            'id' => $applicationData->id,
            'full_name' => $applicationData->applicant->full_name,
            'father_name' => $applicationData->applicant->father_name,
            'date_of_birth' => $applicationData->date_of_birth,
            'photo' => $applicationData->applicant->photo,
            'email' => $applicationData->applicant->email,
            'phone' => $applicationData->applicant->phone,
            'last_education' => $applicationData->last_education,
            'last_institute' => $applicationData->last_institute,
            'month_of_exprience' => $applicationData->month_of_exprience,
            'cnic' => $applicationData->applicant->cnic,
            'address' => $applicationData->applicant->address,
            'expected_salary' => $applicationData->expected_salary,
            'available_from' => $applicationData->available_from,
            'gender' => $applicationData->applicant->gender,
            'status' => $applicationData->status,

            'job_title' => $applicationData->jobPosting?->designation->name ?? 'N/A',

            'department' => $applicationData->jobPosting?->department?->name ?? 'N/A',

            'employment_type' => $applicationData->jobPosting?->employment_type ?? 'N/A',

            'minimum_salary' => $applicationData->jobPosting?->minimum_salary,

            'maximum_salary' => $applicationData->jobPosting?->maximum_salary,

            'min_experience' => $applicationData->jobPosting?->min_experience,

            'created_at' => $applicationData->created_at?->format('d M Y h:i A'),
        ];
        $interview = $applicationData->interview;
        if ($applicationData->interview) {
            $interviewData = $applicationData->interview;

            $this->interview = [
                'id' => $interviewData->id,
                'interviewer_id' => $interviewData->interviewer_id,
                'interviewer_name' => $interviewData->interviewer?->name ?? 'N/A',
                'interviewer_email' => $interviewData->interviewer?->email ?? 'N/A',
                'scheduled_at' => $interviewData->scheduled_at?->format('d M Y h:i A'),
                'type' => $interviewData->type ?? null,
                'meeting_link' => $interviewData->meeting_link ?? null,
                'status' => $interviewData->status ?? 'pending',
            ];
        }

        $this->educations = $applicationData->educations
            ->map(function ($education) {
                return [
                    'id' => $education->id,
                    'graduate_start_year' => $education->graduate_start_year,
                    'graduate_end_year' => $education->graduate_end_year,
                    'institute_type' => $education->institute_type,
                    'grade' => $education->grade,
                    'degree_name' => $education->degree_name,
                    'institute' => $education->institute,
                    'certificate_path' => $education->certificate_path,
                ];
            })
            ->values()
            ->toArray();

        $this->workExperiences = $applicationData->works
            ->map(function ($experience) {
                return [
                    'id' => $experience->id,
                    'month_of_experience' => $experience->month_of_experience,
                    'designation' => $experience->designation,
                    'company' => $experience->company,
                    'start_date' => $experience->start_date,
                    'end_date' => $experience->end_date,
                    'experience_type' => $experience->experience_type,
                    'responsibility' => $experience->responsibility,
                    'benefits' => $experience->benefits,
                ];
            })
            ->values()
            ->toArray();

        $this->documents = $applicationData->documents
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'document_type' => $document->document_type,
                    'file_name' => $document->file_name,
                    'file_path' => $document->file_path,
                    'mime_type' => $document->mime_type,
                    'file_size' => $document->file_size,
                    'remarks' => $document->remarks,
                ];
            })
            ->values()
            ->toArray();
    }
};
?>

<div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Job Application Details</h3>
            <p class="text-muted mb-0">
                Candidate and applied job information
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('jobs.applications.edit', $application['id']) }}" class="btn btn-primary rounded-pill">
                Edit
            </a>

            <a href="{{ route('jobs.applications.index') }}" class="btn btn-secondary rounded-pill">
                Back
            </a>

        </div>
    </div>


    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Application ID
                    </h6>

                    <h3 class="fw-bold text-primary">
                        #{{ $application['id'] }}
                    </h3>

                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Experience
                    </h6>

                    <h3 class="fw-bold text-success">
                        {{ round($application['month_of_exprience'] / 12) ?? 0 }}
                    </h3>

                    <small class="text-muted">

                        Years
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Expected Salary
                    </h6>

                    <h4 class="fw-bold">
                        @if ($application['expected_salary'])
                            Rs.
                            {{ number_format($application['expected_salary']) }}
                        @else
                            N/A
                        @endif
                    </h4>

                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Status
                    </h6>

                    @php
                        $statusClass = match ($application['status']) {
                            'shortlisted' => 'bg-info',
                            'interview' => 'bg-primary',
                            'rejected' => 'bg-danger',
                            default => 'bg-warning text-dark',
                        };
                    @endphp

                    <span class="badge {{ $statusClass }} fs-6">
                        {{ ucfirst($application['status']) }}
                    </span>

                </div>
            </div>
        </div>

    </div>


    {{-- Candidate Information --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Candidate Information
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                {{-- Photo --}}
                <div class="col-md-3 text-center mb-4">

                    @if (!empty($application['photo']))
                        <img src="{{ asset('storage/applicant/' . $application['photo']) }}"
                            alt="{{ $application['full_name'] }}" class="img-thumbnail rounded-4"
                            style="
                                width: 180px;
                                height: 200px;
                                object-fit: cover;
                            ">
                    @else
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mx-auto"
                            style="
                                width:180px;
                                height:200px;
                            ">
                            <i class="bi bi-person fs-1 text-muted"></i>
                        </div>
                    @endif

                </div>


                <div class="col-md-9">

                    <div class="row g-4">

                        <div class="col-md-6">
                            <strong>Full Name</strong>

                            <p class="text-muted mb-0">
                                {{ $application['full_name'] }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Father Name</strong>

                            <p class="text-muted mb-0">
                                {{ $application['father_name'] }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Email</strong>

                            <p class="text-muted mb-0">
                                {{ $application['email'] }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Phone</strong>

                            <p class="text-muted mb-0">
                                {{ $application['phone'] }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>CNIC</strong>

                            <p class="text-muted mb-0">
                                {{ $application['cnic'] }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Gender</strong>

                            <p class="text-muted mb-0">
                                {{ ucfirst($application['gender']) }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Date of Birth</strong>

                            <p class="text-muted mb-0">
                                {{ date('d-M-Y', strtotime($application['date_of_birth'])) }}
                            </p>
                        </div>


                        <div class="col-md-6">
                            <strong>Available From</strong>

                            <p class="text-muted mb-0">
                                {{ date('d-M-Y', strtotime($application['available_from'])) ?? 'N/A' }}
                            </p>
                        </div>


                        <div class="col-12">
                            <strong>Address</strong>

                            <p class="text-muted mb-0">
                                {{ $application['address'] }}
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
                Applied Job
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">
                    <strong>Job Title</strong>

                    <p class="text-muted mb-0">
                        {{ $application['job_title'] }}
                    </p>
                </div>


                <div class="col-md-6">
                    <strong>Department</strong>

                    <p class="text-muted mb-0">
                        {{ $application['department'] }}
                    </p>
                </div>


                <div class="col-md-6">
                    <strong>Employment Type</strong>

                    <p class="text-muted mb-0">
                        {{ ucfirst($application['employment_type']) }}
                    </p>
                </div>


                <div class="col-md-6">
                    <strong>Minimum Experience</strong>

                    <p class="text-muted mb-0">
                        {{ $application['min_experience'] ?? 0 }}
                        Year(s)
                    </p>
                </div>


                <div class="col-md-6">
                    <strong>Minimum Salary</strong>

                    <p class="text-muted mb-0">

                        @if ($application['minimum_salary'])
                            Rs.
                            {{ number_format($application['minimum_salary']) }}
                        @else
                            N/A
                        @endif

                    </p>
                </div>


                <div class="col-md-6">
                    <strong>Maximum Salary</strong>

                    <p class="text-muted mb-0">

                        @if ($application['maximum_salary'])
                            Rs.
                            {{ number_format($application['maximum_salary']) }}
                        @else
                            N/A
                        @endif

                    </p>
                </div>

            </div>

        </div>
    </div>






    {{-- Education --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                Education
            </h5>
        </div>

        <div class="card-body table-responsive">
            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Degree</th>
                        <th>Institute</th>
                        <th>Institute Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Grade</th>
                        <th>Certificate</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($educations as $education)
                        <tr>

                            <td>
                                {{ $education['degree_name'] ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $education['institute'] }}
                            </td>

                            <td>
                                {{ $education['institute_type'] }}
                            </td>

                            <td>
                                {{ date('d-M-Y', strtotime($education['graduate_start_year'])) }}
                            </td>

                            <td>
                                {{ date('d-M-Y', strtotime($education['graduate_end_year'])) ?? '--' }}
                            </td>

                            <td>
                                {{ $education['grade'] }}
                            </td>

                            <td>

                                @if (!empty($education['certificate_path']))
                                    <a href="{{ asset('storage/' . $education['certificate_path']) }}" target="_blank"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        View
                                    </a>
                                @else
                                    <span class="text-muted">
                                        No Certificate
                                    </span>
                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No education records found.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

    {{-- Work Experience --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Work Experience
            </h5>
        </div>

        <div class="card-body table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Designation</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Experience</th>
                        <th>Responsibility</th>
                        <th>Benefits</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($workExperiences as $experience)
                        <tr>

                            <td>
                                {{ $experience['company'] }}
                            </td>

                            <td>
                                {{ $experience['designation'] }}
                            </td>

                            <td>
                                {{ ucfirst($experience['experience_type'] ?? '') }}
                            </td>

                            <td>
                                {{ date('d-M-Y', strtotime($experience['start_date'])) }}
                            </td>

                            <td>
                                {{ date('d-M-Y', strtotime($experience['end_date'])) ?? 'Present' }}
                            </td>

                            <td>
                                {{ round($experience['month_of_experience'] / 12) }}
                                Year
                            </td>

                            <td>
                                {{ $experience['responsibility'] ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $experience['benefits'] ?? 'N/A' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No work experience found.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Documents --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Documents
            </h5>
        </div>

        <div class="card-body table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Type</th>
                        <th>File Name</th>
                        <th>MIME Type</th>
                        <th>Size</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($documents as $document)
                        <tr>

                            <td>
                                <span class="badge bg-primary">
                                    {{ ucwords(str_replace('_', ' ', $document['document_type'])) }}
                                </span>
                            </td>

                            <td>
                                {{ $document['file_name'] }}
                            </td>

                            <td>
                                {{ $document['mime_type'] ?? 'N/A' }}
                            </td>

                            <td>
                                @if (!empty($document['file_size']))
                                    {{ number_format($document['file_size'] / 1024, 2) }}
                                    KB
                                @else
                                    N/A
                                @endif
                            </td>

                            <td>
                                {{ $document['remarks'] ?? 'N/A' }}
                            </td>

                            <td>

                                <a href="{{ asset($document['file_path']) }}" target="_blank"
                                    class="btn btn-sm btn-primary rounded-pill">
                                    View
                                </a>

                                <a href="{{ asset($document['file_path']) }}" download
                                    class="btn btn-sm btn-success rounded-pill">
                                    Download
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No documents uploaded.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Other Information --}}
    <div class="card border-0 shadow">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Application Information
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">

                    <strong>Applied On</strong>

                    <p class="text-muted mb-0">
                        {{ $application['created_at'] }}
                    </p>

                </div>


                <div class="col-md-6">

                    <strong>Current Status</strong>

                    <div class="mt-1">

                        @php
                            $statusClass = match ($application['status']) {
                                'shortlisted' => 'bg-info',
                                'interview' => 'bg-primary',
                                'rejected' => 'bg-danger',
                                default => 'bg-warning text-dark',
                            };
                        @endphp

                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst($application['status']) }}
                        </span>

                    </div>

                </div>

            </div>

        </div>
    </div>

    @if ($application['status'] === 'interview')

        <div class="card border-0 shadow mb-4">

            <div class="card-header bg-light">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>
                        Interview Information
                    </h5>

                    @php
                        $interviewStatusClass = 'bg-primary';

                        if (($interview['status'] ?? '') === 'completed') {
                            $interviewStatusClass = 'bg-success';
                        } elseif (($interview['status'] ?? '') === 'cancelled') {
                            $interviewStatusClass = 'bg-danger';
                        } elseif (($interview['status'] ?? '') === 'rescheduled') {
                            $interviewStatusClass = 'bg-warning text-dark';
                        }
                    @endphp

                    <span class="badge {{ $interviewStatusClass }}">
                        {{ ucfirst($interview['status']) }}
                    </span>

                </div>

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


                    {{-- Interviewer Email --}}
                    <div class="col-md-6">

                        <strong>
                            Interviewer Email
                        </strong>

                        <p class="text-muted mb-0">
                            {{ $interview['interviewer_email'] }}
                        </p>

                    </div>


                    {{-- Scheduled Date --}}
                    <div class="col-md-6">

                        <strong>
                            Scheduled At
                        </strong>

                        <p class="text-muted mb-0">

                            <i class="bi bi-calendar-event me-1"></i>

                            {{ $interview['scheduled_at'] ?? 'N/A' }}

                        </p>

                    </div>


                    {{-- Interview Type --}}
                    <div class="col-md-6">

                        <strong>
                            Interview Type
                        </strong>

                        <p class="mt-1 mb-0">

                            @php
                                $typeClass = match ($interview['type']) {
                                    'online' => 'bg-info',
                                    'physical' => 'bg-success',
                                    'phone' => 'bg-secondary',
                                    default => 'bg-dark',
                                };
                            @endphp

                            <span class="badge {{ $typeClass }}">
                                {{ ucfirst($interview['type']) }}
                            </span>

                        </p>

                    </div>


                    {{-- Meeting Link --}}
                    @if ($interview['type'] === 'online' && !empty($interview['meeting_link']))
                        <div class="col-md-6">

                            <strong>
                                Meeting Link
                            </strong>

                            <p class="mb-0 mt-1">

                                <a href="{{ $interview['meeting_link'] }}" target="_blank"
                                    class="btn btn-sm btn-primary rounded-pill">
                                    <i class="bi bi-camera-video me-1"></i>
                                    Join Interview
                                </a>

                            </p>

                        </div>
                    @endif


                    {{-- Interview Status --}}
                    <div class="col-md-6">

                        <strong>
                            Interview Status
                        </strong>

                        <p class="mt-1 mb-0">

                            <span class="badge {{ $interviewStatusClass }}">
                                {{ ucfirst($interview['status']) }}
                            </span>

                        </p>

                    </div>


                    {{-- Rating --}}
                    @if (!empty($interview['rating']))
                        <div class="col-md-6">

                            <strong>
                                Rating
                            </strong>

                            <p class="text-muted mb-0">

                                {{ $interview['rating'] }} / 5

                                <i class="bi bi-star-fill text-warning"></i>

                            </p>

                        </div>
                    @endif


                    {{-- Feedback --}}
                    @if (!empty($interview['feedback']))
                        <div class="col-12">

                            <strong>
                                Interview Feedback
                            </strong>

                            <div class="bg-light rounded p-3 mt-2">

                                {{ $interview['feedback'] }}

                            </div>

                        </div>
                    @endif

                </div>

            </div>

        </div>

    @endif


</div>
