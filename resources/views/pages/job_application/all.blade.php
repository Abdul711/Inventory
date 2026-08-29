<?php

use Livewire\Component;
use App\Models\JobApplication;

new class extends Component {
    public string $search = '';

    public array $applications = [];

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function updatedSearch(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $this->applications = JobApplication::with(['jobPosting.department', 'applicant', 'JobPosting.designation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhereHas('jobPosting', function ($job) {
                            $job->where('job_title', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('jobPosting.department', function ($department) {
                            $department->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->get()
            ->toArray();
    }
};
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Job Applications</h3>
            <p class="text-muted mb-0">
                Manage candidate job applications
            </p>
        </div>

    </div>

    <div class="dashboard-card">

        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search candidate, job title or department...">

        <div class="table-responsive">
            <table class="table align-middle">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Candidate</th>
                        <th>Job Title</th>

                        <th>Email</th>
                        <th>Phone</th>

                        <th>Status</th>
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
                                <div class="d-flex align-items-center gap-2">

                                    @if (!empty($application['applicant']['photo']))
                                        <img src="{{ asset('storage/applicant/' . $application['applicant']['photo']) }}"
                                            width="45" height="45" class="rounded-circle object-fit-cover"
                                            alt="{{ $application['applicant']['full_name'] }}">
                                    @endif

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $application['applicant']['full_name'] }}
                                        </div>

                                        <small class="text-muted">
                                            {{ ucfirst($application['applicant']['gender']) }}
                                        </small>
                                    </div>

                                </div>
                            </td>

                            <td>
                                {{ $application['job_posting']['designation']['name'] ?? '-' }}
                            </td>


                            <td>
                                {{ $application['applicant']['email'] }}
                            </td>

                            <td>
                                {{ $application['applicant']['phone'] }}
                            </td>





                            <td>

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

                            </td>

                            <td>
                                <div class="d-flex gap-1">

                                    <a href="{{ route('jobs.applications.edit', $application['id']) }}"
                                        class="btn btn-sm btn-info rounded-pill text-white">
                                        Edit
                                    </a>

                                    <a href="{{ route('jobs.applications.show', $application['id']) }}"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        View
                                    </a>

                                    <button type="button" class="btn btn-sm btn-danger rounded-pill">
                                        Delete
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No job applications found.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>
</div>
