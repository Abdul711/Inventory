<?php

use Livewire\Component;
use App\Models\Applicant;

new class extends Component {
    public int $id;

    public array $applicant = [];
    public array $applications = [];
    public array $educations = [];
    public array $workExperiences = [];
    public array $documents = [];

    public function mount($id): void
    {
        $this->id = (int) $id;

        $applicantData = Applicant::with(['jobApplications.jobPosting.department', 'jobApplications.interview.interviewer', 'educations', 'works', 'documents'])->findOrFail($this->id);
        $this->workExperiences = $applicantData->works->toArray();
        $this->educations = $applicantData->educations->toArray();
        $this->documents = $applicantData->documents->toArray();

        /*
        |--------------------------------------------------------------------------
        | Applicant Information
        |--------------------------------------------------------------------------
        */

        $this->applications = $applicantData->jobApplications
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'status' => $application->status,
                    'job_title' => $application->jobPosting?->designation->name ?? 'N/A',
                    'department' => $application->jobPosting?->department?->name ?? 'N/A',
                    'created_at' => $application->created_at,
                    'interview' => $application->interview
                        ? [
                            'id' => $application->interview->id,
                            'scheduled_at' => $application->interview->scheduled_at?->format('d M Y h:i A'),
                            'type' => $application->interview->type,
                            'status' => $application->interview->status,
                            'meeting_link' => $application->interview->meeting_link,
                            'interviewer' => $application->interview->interviewer?->name ?? 'N/A',
                        ]
                        : null,
                ];
            })
            ->values()
            ->toArray();

        $this->applicant = [
            'id' => $applicantData->id,
            'full_name' => $applicantData->full_name,
            'father_name' => $applicantData->father_name,
            'email' => $applicantData->email,
            'phone' => $applicantData->phone,
            'cnic' => $applicantData->cnic,
            'gender' => $applicantData->gender,
            'address' => $applicantData->address,
            'photo' => $applicantData->photo,
            'created_at' => $applicantData->created_at?->format('d M Y h:i A'),
        ];
    }
};

?>

<div>

    {{-- Header --}}
    <div class="d-flex justify-content-between
        align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Applicant Details
            </h3>

            <p class="text-muted mb-0">
                Applicant profile and application history
            </p>

        </div>


        <div class="d-flex gap-2">



            <a href="{{ route('jobs.applicants.index') }}" class="btn btn-secondary rounded-pill">
                Back
            </a>

        </div>

    </div>


    {{-- Summary --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Applicant ID
                    </h6>

                    <h3 class="fw-bold text-primary">

                        #{{ $applicant['id'] }}

                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Total Applications
                    </h6>

                    <h3 class="fw-bold text-success">

                        {{ count($applications) }}

                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Registered On
                    </h6>

                    <div class="fw-bold">

                        {{ $applicant['created_at'] ?? 'N/A' }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Applicant Information --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">
                <i class="bi bi-person me-2"></i>

                Applicant Information
            </h5>

        </div>


        <div class="card-body">

            <div class="row">

                {{-- Photo --}}
                <div class="col-md-3 text-center mb-4">

                    @if (!empty($applicant['photo']))
                        <img src="{{ asset('storage/applicant/' . $applicant['photo']) }}"
                            alt="{{ $applicant['full_name'] }}" class="img-thumbnail rounded-4"
                            style="
                                width:180px;
                                height:200px;
                                object-fit:cover;
                            ">
                    @else
                        <div class="bg-light rounded-4
                                d-flex align-items-center
                                justify-content-center mx-auto"
                            style="
                                width:180px;
                                height:200px;
                            ">

                            <i class="bi bi-person
                                fs-1 text-muted"></i>

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

                                {{ $applicant['father_name'] ?? 'N/A' }}

                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Email
                            </strong>

                            <p class="text-muted mb-0">

                                {{ $applicant['email'] ?? 'N/A' }}

                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Phone
                            </strong>

                            <p class="text-muted mb-0">

                                {{ $applicant['phone'] ?? 'N/A' }}

                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                CNIC
                            </strong>

                            <p class="text-muted mb-0">

                                {{ $applicant['cnic'] ?? 'N/A' }}

                            </p>

                        </div>


                        <div class="col-md-6">

                            <strong>
                                Gender
                            </strong>

                            <p class="text-muted mb-0">

                                {{ ucfirst($applicant['gender'] ?? 'N/A') }}

                            </p>

                        </div>


                        <div class="col-12">

                            <strong>
                                Address
                            </strong>

                            <p class="text-muted mb-0">

                                {{ $applicant['address'] ?? 'N/A' }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Job Applications --}}
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0">

                <i class="bi bi-briefcase me-2"></i>

                Job Applications

            </h5>

        </div>


        <div class="card-body table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Job Title</th>

                        <th>Department</th>

                        <th>Applied On</th>

                        <th>Status</th>

                        <th>Interview</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse ($applications as $application)
                        <tr>

                            <td>
                                #{{ $application['id'] }}
                            </td>


                            <td>

                                <div class="fw-semibold">

                                    {{ $application['job_title'] }}

                                </div>

                                <small class="text-muted">

                                    {{ ucfirst($application['employment_type'] ?? '') }}

                                </small>

                            </td>


                            <td>

                                {{ $application['department'] }}

                            </td>


                            <td>

                                {{ $application['created_at'] }}

                            </td>


                            {{-- Application Status --}}
                            <td>

                                @php

                                    $statusClass = match ($application['status'] ?? '') {
                                        'shortlisted' => 'bg-info',

                                        'interview' => 'bg-primary',

                                        'hired' => 'bg-success',

                                        'rejected' => 'bg-danger',

                                        default => 'bg-warning text-dark',
                                    };

                                @endphp

                                <span class="badge
                                    {{ $statusClass }}">

                                    {{ ucfirst($application['status']) }}

                                </span>

                            </td>


                            {{-- Interview --}}
                            <td>

                                @if (!empty($application['interview']))
                                    <div class="fw-semibold">

                                        {{ $application['interview']['scheduled_at'] ?? 'N/A' }}

                                    </div>


                                    <small class="text-muted">

                                        {{ ucfirst($application['interview']['type'] ?? '') }}

                                        -

                                        {{ $application['interview']['interviewer'] ?? 'N/A' }}

                                    </small>
                                @else
                                    <span class="text-muted">
                                        No Interview
                                    </span>
                                @endif

                            </td>


                            {{-- Action --}}
                            <td>

                                <a href="{{ route('jobs.applications.show', $application['id']) }}"
                                    class="btn btn-sm
                                    btn-primary rounded-pill">
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center
                                text-muted py-4">
                                No applications found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

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
                        <th>Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Grade</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($educations as $education)
                        <tr>

                            <td>
                                {{ $education['degree_name'] ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $education['institute'] ?? 'N/A' }}
                            </td>

                            <td>

                                {{ ucfirst($education['institute_type'] ?? '') }}

                            </td>

                            <td>

                                {{ $education['graduate_start_year'] ?? 'N/A' }}

                            </td>

                            <td>

                                {{ $education['graduate_end_year'] ?? 'N/A' }}

                            </td>

                            <td>

                                {{ $education['grade'] ?? 'N/A' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center
                                text-muted py-4">

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
                        <th>Experience</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($workExperiences
                        as $experience)
                        <tr>

                            <td>

                                {{ $experience['company'] ?? 'N/A' }}

                            </td>


                            <td>

                                {{ $experience['designation'] ?? 'N/A' }}

                            </td>


                            <td>

                                {{ ucfirst($experience['experience_type'] ?? '') }}

                            </td>


                            <td>

                                {{ round(($experience['month_of_experience'] ?? 0) / 12, 1) }}

                                Year(s)

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center
                                text-muted py-4">
                                No work experience found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Documents --}}
    <div class="card border-0 shadow">

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
                        <th>File</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($documents as $document)
                        <tr>

                            <td>

                                {{ ucwords(str_replace('_', ' ', $document['document_type'])) }}

                            </td>


                            <td>

                                {{ $document['file_name'] }}

                            </td>


                            <td>

                                {{ $document['remarks'] ?? 'N/A' }}

                            </td>


                            <td>

                                <a href="{{ asset($document['file_path']) }}" target="_blank"
                                    class="btn btn-sm
                                    btn-primary rounded-pill">
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

                            <td colspan="4" class="text-center
                                text-muted py-4">
                                No documents found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
