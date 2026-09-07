<?php

use Livewire\Component;
use App\Models\JobOffer;

new class extends Component {
    public int $id;

    public array $offer = [];

    public function mount($id): void
    {
        $this->id = (int) $id;

        $offerData = JobOffer::with(['applicant', 'jobApplication.jobPosting.department', 'jobApplication.jobPosting.designation', 'createdBy', 'approvedBy'])->findOrFail($this->id);

        $this->offer = [
            'id' => $offerData->id,

            'offer_number' => $offerData->offer_number,

            // Applicant
            'applicant_id' => $offerData->applicant_id,
            'applicant_name' => $offerData->applicant?->full_name ?? 'N/A',
            'applicant_email' => $offerData->applicant?->email ?? 'N/A',
            'applicant_phone' => $offerData->applicant?->phone ?? 'N/A',

            // Job
            'job_application_id' => $offerData->job_application_id,

            'department' => $offerData->jobApplication?->jobPosting?->department?->name ?? 'N/A',

            'designation' => $offerData->jobApplication?->jobPosting?->designation?->name ?? 'N/A',

            // Salary
            'salary_proposed' => $offerData->salary_proposed,
            'approved_salary' => $offerData->approved_salary,
            'candidate_expected_salary' => $offerData->candidate_expected_salary,

            // Negotiation
            'candidate_negotiation_note' => $offerData->candidate_negotiation_note,

            'negotiated_at' => $offerData->negotiated_at?->format('d M Y h:i A'),

            // Offer
            'offer_date' => $offerData->offer_date?->format('d M Y'),

            'expiry_date' => $offerData->expiry_date?->format('d M Y'),

            'contract_start_date' => $offerData->contract_start_date?->format('d M Y'),

            'probation_months' => $offerData->probation_months,
            'notice_period_days' => $offerData->notice_period_days,
            'duty_durations' => $offerData->duty_durations,

            // Status
            'status' => $offerData->status,

            // Approval
            'approved_at' => $offerData->approved_at?->format('d M Y h:i A'),

            'accepted_at' => $offerData->accepted_at?->format('d M Y h:i A'),

            // Withdrawal
            'withdrawn_at' => $offerData->withdrawn_at?->format('d M Y h:i A'),

            'withdrawal_reason' => $offerData->withdrawal_reason,

            // Users
            'created_by' => $offerData->createdBy?->name ?? 'N/A',

            'approved_by' => $offerData->approvedBy?->name ?? 'N/A',

            // Letter
            'offer_letter_path' => $offerData->offer_letter_path,

            'created_at' => $offerData->created_at?->format('d M Y h:i A'),
        ];
    }
};
?>

<div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Job Offer Details
            </h3>

            <p class="text-muted mb-0">
                Offer information, salary, applicant and employment terms
            </p>
        </div>

        <a href="{{ route('jobs.offers.index') }}" class="btn btn-secondary rounded-pill">
            Back
        </a>

    </div>


    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        {{-- Proposed Salary --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Proposed Salary
                    </h6>

                    <h4 class="fw-bold text-primary">

                        Rs.
                        {{ number_format($offer['salary_proposed'] ?? 0, 2) }}

                    </h4>

                </div>

            </div>

        </div>


        {{-- Approved Salary --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Approved Salary
                    </h6>

                    <h4 class="fw-bold text-success">

                        @if ($offer['approved_salary'])
                            Rs.
                            {{ number_format($offer['approved_salary'], 2) }}
                        @else
                            N/A
                        @endif

                    </h4>

                </div>

            </div>

        </div>


        {{-- Probation --}}
        <div class="col-md-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Probation
                    </h6>

                    <h4 class="fw-bold">

                        {{ $offer['probation_months'] }}
                        Months

                    </h4>

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

                    @switch($offer['status'])
                        @case('accepted')
                            <span class="badge bg-success fs-6">
                                Accepted
                            </span>
                        @break

                        @case('declined')
                            <span class="badge bg-danger fs-6">
                                Declined
                            </span>
                        @break

                        @default
                            <span class="badge bg-warning text-dark fs-6">
                                Pending
                            </span>
                    @endswitch

                </div>

            </div>

        </div>

    </div>


    {{-- Applicant Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0 fw-bold">
                Applicant Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>Applicant:</strong>

                    <div>
                        {{ $offer['applicant_name'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Email:</strong>

                    <div>
                        {{ $offer['applicant_email'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Phone:</strong>

                    <div>
                        {{ $offer['applicant_phone'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Department:</strong>

                    <div>
                        {{ $offer['department'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Designation:</strong>

                    <div>
                        {{ $offer['designation'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Application ID:</strong>

                    <div>

                        #{{ $offer['job_application_id'] }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Salary Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0 fw-bold">
                Salary & Negotiation
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>Candidate Expected Salary</strong>

                    <div>

                        @if ($offer['candidate_expected_salary'])
                            Rs.
                            {{ number_format($offer['candidate_expected_salary'], 2) }}
                        @else
                            N/A
                        @endif

                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Salary Proposed</strong>

                    <div>

                        Rs.
                        {{ number_format($offer['salary_proposed'], 2) }}

                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Approved Salary</strong>

                    <div>

                        @if ($offer['approved_salary'])
                            Rs.
                            {{ number_format($offer['approved_salary'], 2) }}
                        @else
                            <span class="text-muted">
                                Not approved yet
                            </span>
                        @endif

                    </div>

                </div>

            </div>


            @if ($offer['candidate_negotiation_note'])

                <hr>

                <strong>
                    Candidate Negotiation Note
                </strong>

                <p class="mb-1 mt-2">

                    {{ $offer['candidate_negotiation_note'] }}

                </p>

                @if ($offer['negotiated_at'])
                    <small class="text-muted">

                        Negotiated:
                        {{ $offer['negotiated_at'] }}

                    </small>
                @endif

            @endif

        </div>

    </div>


    {{-- Employment Terms --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0 fw-bold">
                Employment Terms
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3 mb-3">

                    <strong>Offer Date</strong>

                    <div>
                        {{ $offer['offer_date'] ?? 'N/A' }}
                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Expiry Date</strong>

                    <div>
                        {{ $offer['expiry_date'] ?? 'N/A' }}
                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Contract Start</strong>

                    <div>
                        {{ $offer['contract_start_date'] ?? 'N/A' }}
                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Duty Duration</strong>

                    <div>

                        {{ $offer['duty_durations'] ?? 'N/A' }}

                        @if ($offer['duty_durations'])
                            Hours
                        @endif

                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Probation Period</strong>

                    <div>

                        {{ $offer['probation_months'] }}
                        Months

                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Notice Period</strong>

                    <div>

                        {{ $offer['notice_period_days'] }}
                        Month

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Offer Approval Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <h5 class="mb-0 fw-bold">
                Approval Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>Created By</strong>

                    <div>
                        {{ $offer['created_by'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Approved By</strong>

                    <div>
                        {{ $offer['approved_by'] }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Approved At</strong>

                    <div>
                        {{ $offer['approved_at'] ?? 'Not Approved' }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Accepted At</strong>

                    <div>
                        {{ $offer['accepted_at'] ?? 'Not Accepted' }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Withdrawal --}}
    @if ($offer['withdrawn_at'])
        <div class="card border-danger shadow-sm mb-4">

            <div class="card-header bg-danger text-white">

                <h5 class="mb-0">
                    Offer Withdrawn
                </h5>

            </div>

            <div class="card-body">

                <p>

                    <strong>Withdrawn At:</strong>

                    {{ $offer['withdrawn_at'] }}

                </p>

                <p class="mb-0">

                    <strong>Reason:</strong>

                    {{ $offer['withdrawal_reason'] ?? 'No reason provided' }}

                </p>

            </div>

        </div>
    @endif


    {{-- Actions --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="d-flex flex-wrap gap-2">

                <a href="{{ route('jobs.offers.edit', $offer['id']) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i>
                    Edit Offer
                </a>


                <a href="{{ route('jobs.applications.show', $offer['job_application_id']) }}"
                    class="btn btn-info text-white">
                    <i class="bi bi-person-vcard me-1"></i>
                    View Application
                </a>


                @if ($offer['offer_letter_path'])
                    <a href="{{ asset('storage/' . $offer['offer_letter_path']) }}" target="_blank"
                        class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        Offer Letter
                    </a>
                @endif

            </div>

        </div>

    </div>

</div>
