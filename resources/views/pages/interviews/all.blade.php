<?php

use Livewire\Component;
use App\Models\Interview;

new class extends Component {
    public string $search = '';

    public array $interviews = [];

    public function mount(): void
    {
        $this->loadInterviews();
    }

    public function updatedSearch(): void
    {
        $this->loadInterviews();
    }

    public function loadInterviews(): void
    {
        $this->interviews = Interview::with(['jobApplication.applicant', 'jobApplication.jobPosting.department', 'jobApplication.jobPosting.designation', 'interviewer'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    // Applicant
                    $q->whereHas('jobApplication.applicant', function ($applicant) {
                        $applicant
                            ->where('full_name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    })

                        // Job title
                        ->orWhereHas('jobApplication.jobPosting', function ($job) {
                            $job->where('job_title', 'like', '%' . $this->search . '%');
                        })

                        // Department
                        ->orWhereHas('jobApplication.jobPosting.department', function ($department) {
                            $department->where('name', 'like', '%' . $this->search . '%');
                        })

                        // Interviewer
                        ->orWhereHas('interviewer', function ($interviewer) {
                            $interviewer->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                        })

                        // Type
                        ->orWhere('type', 'like', '%' . $this->search . '%')

                        // Status
                        ->orWhere('status', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('scheduled_at')
            ->get()
            ->toArray();
    }
};

?>
<div>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Interviews
            </h3>

            <p class="text-muted mb-0">
                Manage candidate interviews
            </p>
        </div>

    </div>


    <div class="dashboard-card">

        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search candidate, job title, interviewer...">


        <div class="table-responsive">

            <table class="table align-middle">

                <thead class="table-light">

                    <tr>
                        <th>ID</th>

                        <th>Candidate</th>

                        <th>Job Title</th>

                        <th>Interviewer</th>

                        <th>Scheduled At</th>

                        <th>Type</th>

                        <th>Meeting</th>

                        <th>Status</th>

                        <th>Action</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($interviews as $interview)
                        <tr>

                            {{-- ID --}}
                            <td>
                                #{{ $interview['id'] }}
                            </td>


                            {{-- Candidate --}}
                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    @if (!empty($interview['job_application']['applicant']['photo']))
                                        <img src="{{ asset('storage/applicant/' . $interview['job_application']['applicant']['photo']) }}"
                                            width="45" height="45" class="rounded-circle object-fit-cover"
                                            alt="">
                                    @endif


                                    <div>

                                        <div class="fw-semibold">

                                            {{ $interview['job_application']['applicant']['full_name'] ?? 'N/A' }}

                                        </div>


                                        <small class="text-muted">

                                            {{ $interview['job_application']['applicant']['email'] ?? '' }}

                                        </small>

                                    </div>

                                </div>

                            </td>


                            {{-- Job Title --}}
                            <td>

                                {{ $interview['job_application']['job_posting']['designation']['name'] ?? 'N/A' }}

                                <br>

                                <small class="text-muted">

                                    {{ $interview['job_application']['job_posting']['department']['name'] ?? '' }}

                                </small>

                            </td>


                            {{-- Interviewer --}}
                            <td>

                                <div class="fw-semibold">

                                    {{ $interview['interviewer']['name'] ?? 'N/A' }}

                                </div>

                                <small class="text-muted">

                                    {{ $interview['interviewer']['email'] ?? '' }}

                                </small>

                            </td>


                            {{-- Scheduled At --}}
                            <td>

                                @if (!empty($interview['scheduled_at']))
                                    {{ \Carbon\Carbon::parse($interview['scheduled_at'])->format('d M Y') }}

                                    <br>

                                    <small class="text-muted">

                                        {{ \Carbon\Carbon::parse($interview['scheduled_at'])->format('h:i A') }}

                                    </small>
                                @else
                                    <span class="text-muted">
                                        N/A
                                    </span>
                                @endif

                            </td>


                            {{-- Type --}}
                            <td>

                                @php
                                    $typeClass = match ($interview['mode'] ?? '') {
                                        'online' => 'bg-info',
                                        'physical' => 'bg-success',
                                        'phone' => 'bg-secondary',
                                        default => 'bg-dark',
                                    };
                                @endphp


                                <span class="badge {{ $typeClass }}">

                                    {{ ucfirst($interview['mode'] ?? 'N/A') }}

                                </span>

                            </td>


                            {{-- Meeting --}}
                            <td>

                                @if (($interview['mode'] ?? '') === 'online' && !empty($interview['meeting_link']))
                                    <a href="{{ $interview['meeting_link'] }}" target="_blank"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        <i class="bi bi-camera-video"></i>

                                        Join
                                    </a>
                                @elseif (($interview['mode'] ?? '') === 'physical')
                                    <span class="text-muted">
                                        Physical
                                    </span>
                                @elseif (($interview['mode'] ?? '') === 'phone')
                                    <span class="text-muted">
                                        Phone
                                    </span>
                                @else
                                    -
                                @endif

                            </td>


                            {{-- Status --}}
                            <td>

                                @php
                                    $statusClass = match ($interview['status'] ?? '') {
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        'rescheduled' => 'bg-warning text-dark',
                                        'pending' => 'bg-warning text-dark',
                                        default => 'bg-primary',
                                    };
                                @endphp


                                <span class="badge {{ $statusClass }}">

                                    {{ ucfirst($interview['status'] ?? 'pending') }}

                                </span>

                            </td>


                            {{-- Actions --}}
                            <td>

                                <div class="d-flex gap-1">

                                    <a href="{{ route('jobs.interviews.edit', $interview['id']) }}"
                                        class="btn btn-sm btn-info rounded-pill text-white">
                                        Edit
                                    </a>


                                    <a href="{{ route('jobs.interviews.show', $interview['id']) }}"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        View
                                    </a>
                                    <a href="{{ route('jobs.interviews.entry-pass', $interview['id']) }}"
                                        class="btn btn-primary">
                                        <i class="fas fa-download"></i>
                                        Download Entry Pass
                                    </a>

                                    <button type="button" class="btn btn-sm btn-danger rounded-pill">
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="9" class="text-center text-muted py-4">
                                No interviews found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
