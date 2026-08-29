<?php

use Livewire\Component;
use App\Models\Applicant;

new class extends Component {
    public string $search = '';

    public array $applicants = [];

    public function mount(): void
    {
        $this->loadApplicants();
    }

    public function updatedSearch(): void
    {
        $this->loadApplicants();
    }

    public function loadApplicants(): void
    {
        $this->applicants = Applicant::with(['jobApplications.jobPosting.department', 'jobApplications.jobPosting.designation'])
            ->withCount('jobApplications')

            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')

                        ->orWhere('email', 'like', '%' . $this->search . '%')

                        ->orWhere('phone', 'like', '%' . $this->search . '%')

                        ->orWhere('cnic', 'like', '%' . $this->search . '%')

                        ->orWhereHas('jobApplications.jobPosting', function ($job) {
                            $job->where('job_title', 'like', '%' . $this->search . '%');
                        })

                        ->orWhereHas('jobApplications.jobPosting.department', function ($department) {
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
            <h3 class="fw-bold mb-1">
                Applicants
            </h3>

            <p class="text-muted mb-0">
                Manage job applicants
            </p>
        </div>

    </div>


    <div class="dashboard-card">

        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search applicant, email, phone, job title or department...">


        <div class="table-responsive">

            <table class="table align-middle">

                <thead class="table-light">

                    <tr>
                        <th>ID</th>

                        <th>Applicant</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>CNIC</th>

                        <th>Applied Jobs</th>

                        <th>Total Applications</th>

                        <th>Action</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($applicants as $applicant)

                        <tr>

                            {{-- ID --}}
                            <td>
                                #{{ $applicant['id'] }}
                            </td>


                            {{-- Applicant --}}
                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    @if (!empty($applicant['photo']))
                                        <img src="{{ asset('storage/applicant/' . $applicant['photo']) }}"
                                            width="45" height="45" class="rounded-circle object-fit-cover"
                                            alt="{{ $applicant['full_name'] }}">
                                    @else
                                        <div class="rounded-circle bg-light
                                                d-flex align-items-center
                                                justify-content-center"
                                            style="width:45px;height:45px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif


                                    <div>

                                        <div class="fw-semibold">
                                            {{ $applicant['full_name'] }}
                                        </div>

                                        <small class="text-muted">

                                            {{ ucfirst($applicant['gender'] ?? '') }}

                                        </small>

                                    </div>

                                </div>

                            </td>


                            {{-- Email --}}
                            <td>

                                {{ $applicant['email'] ?? 'N/A' }}

                            </td>


                            {{-- Phone --}}
                            <td>

                                {{ $applicant['phone'] ?? 'N/A' }}

                            </td>


                            {{-- CNIC --}}
                            <td>

                                {{ $applicant['cnic'] ?? 'N/A' }}

                            </td>


                            {{-- Applied Jobs --}}
                            <td>

                                @forelse ($applicant['job_applications']
                                    as $application)
                                    <div class="mb-1">

                                        <span class="fw-semibold">

                                            {{ $application['job_posting']['designation']['name'] ?? 'N/A' }}

                                        </span>

                                        @if (!empty($application['job_posting']['department']['name']))
                                            <br>

                                            <small class="text-muted">

                                                {{ $application['job_posting']['department']['name'] }}

                                            </small>
                                        @endif

                                    </div>

                                @empty

                                    <span class="text-muted">
                                        No applications
                                    </span>
                                @endforelse

                            </td>


                            {{-- Total Applications --}}
                            <td>

                                <span class="badge bg-primary">

                                    {{ $applicant['job_applications_count'] ?? 0 }}

                                </span>

                            </td>


                            {{-- Actions --}}
                            <td>

                                <div class="d-flex gap-1">
                                    {{--
                                    <a href="{{ route('jobs.applicants.edit', $applicant['id']) }}"
                                        class="btn btn-sm btn-info
                                            rounded-pill text-white">
                                        Edit
                                    </a>
--}}

                                    <a href="{{ route('jobs.applicants.show', $applicant['id']) }}"
                                        class="btn btn-sm btn-primary
                                            rounded-pill">
                                        View
                                    </a>


                                    <button type="button"
                                        class="btn btn-sm btn-danger
                                            rounded-pill">
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center text-muted py-4">
                                No applicants found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
