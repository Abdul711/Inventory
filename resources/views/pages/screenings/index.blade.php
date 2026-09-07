<?php

use Livewire\Component;
use App\Models\Screening;

new class extends Component {
    public string $search = '';

    public array $screenings = [];

    public function mount(): void
    {
        $this->loadScreenings();
    }

    public function updatedSearch(): void
    {
        $this->loadScreenings();
    }

    public function loadScreenings(): void
    {
        $this->screenings = Screening::query()
            ->with(['jobApplication.applicant', 'jobApplication.jobPosting.designation', 'screenedBy'])
            ->when($this->search, function ($query) {
                $query
                    ->whereHas('jobApplication.applicant', function ($q) {
                        $q->where('full_name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                    })

                    ->orWhereHas('jobApplication.jobPosting.designation', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->get()
            ->toArray();
    }
};
?>

<div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Applicant Screenings</h3>
            <p class="text-muted mb-0">
                Manage applicant screening results
            </p>
        </div>
    </div>

    <div class="dashboard-card">

        {{-- Search --}}
        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search applicant or designation...">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>CV</th>
                        <th>Experience</th>
                        <th>Education</th>
                        <th>Overall</th>
                        <th>Status</th>
                        <th>Screened By</th>
                        <th>Screened At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($screenings as $screening)

                        @php
                            $application = $screening['job_application'] ?? [];

                            $applicant = $application['applicant'] ?? [];

                            $jobPosting = $application['job_posting'] ?? [];

                            $designation = $jobPosting['designation'] ?? [];

                            $screenedBy = $screening['screened_by'] ?? [];

                            $overallScore =
                                $screening['overall_score'] ??
                                round(
                                    (($screening['cv_score'] ?? 0) +
                                        ($screening['experience_score'] ?? 0) +
                                        ($screening['education_score'] ?? 0)) /
                                        3,
                                    2,
                                );
                        @endphp

                        <tr>

                            {{-- Screening ID --}}
                            <td>
                                #{{ $screening['id'] }}
                            </td>

                            {{-- Applicant --}}
                            <td>

                                <img src="{{ asset('storage/applicant/' . $applicant['photo']) }}" width="45"
                                    height="45" class="rounded-circle object-fit-cover"
                                    alt="{{ $applicant['full_name'] }}">
                                <div class="fw-semibold">
                                    {{ $applicant['full_name'] ?? 'N/A' }}
                                </div>
                                <small class="text-muted">
                                    {{ $applicant['email'] ?? '' }}
                                </small>
                            </td>

                            {{-- Job --}}
                            <td>
                                {{ $designation['name'] ?? 'N/A' }}
                            </td>

                            {{-- CV Score --}}
                            <td>
                                <span class="badge bg-primary">
                                    {{ $screening['cv_score'] ?? 0 }}/5
                                </span>
                            </td>

                            {{-- Experience --}}
                            <td>
                                <span class="badge bg-primary">
                                    {{ $screening['experience_score'] ?? 0 }}/5
                                </span>
                            </td>

                            {{-- Education --}}
                            <td>
                                <span class="badge bg-primary">
                                    {{ $screening['education_score'] ?? 0 }}/5
                                </span>
                            </td>

                            {{-- Overall --}}
                            <td>

                                @if ($overallScore >= 4)
                                    <span class="badge bg-success">
                                        {{ number_format($overallScore, 2) }}
                                    </span>
                                @elseif ($overallScore >= 3)
                                    <span class="badge bg-warning text-dark">
                                        {{ number_format($overallScore, 2) }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        {{ number_format($overallScore, 2) }}
                                    </span>
                                @endif

                            </td>

                            {{-- Status --}}
                            <td>

                                @if (($screening['status'] ?? '') === 'completed')
                                    <span class="badge bg-success">
                                        Completed
                                    </span>
                                @elseif (($screening['status'] ?? '') === 'rejected')
                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>
                                @elseif (($screening['status'] ?? '') === 'shortlisted')
                                    <span class="badge bg-info">
                                        Shortlisted
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        {{ ucfirst($screening['status'] ?? 'pending') }}
                                    </span>
                                @endif

                            </td>

                            {{-- Screened By --}}
                            <td>
                                {{ $screenedBy['name'] ?? 'N/A' }}
                            </td>

                            {{-- Screened Date --}}
                            <td>
                                {{ $screening['screened_at'] ?? 'N/A' }}
                            </td>

                            {{-- Actions --}}
                            <td>

                                <a href="{{ route('jobs.screenings.show', $screening['id']) }}"
                                    class="btn btn-sm btn-info rounded-pill text-white">
                                    View
                                </a>

                                <a href="{{ route('jobs.screenings.edit', $screening['id']) }}"
                                    class="btn btn-sm btn-primary rounded-pill">
                                    Edit
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No screening records found.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
</div>
