<?php

use Livewire\Component;
use App\Models\JobOffer;

new class extends Component {
    public string $search = '';

    public array $jobOffers = [];

    public function mount(): void
    {
        $this->loadJobOffers();
    }

    public function updatedSearch(): void
    {
        $this->loadJobOffers();
    }

    public function loadJobOffers(): void
    {
        $this->jobOffers = JobOffer::with(['applicant', 'jobApplication.jobPosting.designation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('offer_number', 'like', '%' . $this->search . '%')
                        ->orWhere('job_application_id', 'like', '%' . $this->search . '%')

                        ->orWhereHas('applicant', function ($applicant) {
                            $applicant->where('full_name', 'like', '%' . $this->search . '%');
                        })

                        ->orWhereHas('jobApplication.jobPosting.designation', function ($designation) {
                            $designation->where('name', 'like', '%' . $this->search . '%');
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
            <h3 class="fw-bold mb-1">Job Offers</h3>
            <p class="text-muted mb-0">
                Manage candidate job offers
            </p>
        </div>
        {{--

        <a href="{{ route('job-offers.create') }}" class="btn btn-primary rounded-pill">
            Add Job Offer
        </a>
        --}}
    </div>

    <div class="dashboard-card">

        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search offer, applicant or designation...">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Offer #</th>
                        <th>Applicant</th>
                        <th>Designation</th>
                        <th>Candidate Expected Salary</th>

                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($jobOffers as $offer)
                        <tr>

                            {{-- Offer Number --}}
                            <td>
                                <span class="fw-semibold">
                                    {{ $offer['offer_number'] }}
                                </span>
                            </td>

                            {{-- Applicant --}}
                            <td>
                                {{ $offer['applicant']['full_name'] ?? 'N/A' }}
                            </td>

                            {{-- Designation --}}
                            <td>
                                {{ $offer['job_application']['job_posting']['designation']['name'] ?? 'N/A' }}
                            </td>


                            <td>{{ $offer['candidate_expected_salary'] }}</td>

                            {{-- Proposed Salary --}}


                            {{-- Approved Salary --}}


                            {{-- Offer Date --}}


                            {{-- Status --}}
                            <td>

                                @php
                                    $status = $offer['status'];

                                    $badge = match ($status) {
                                        'accepted' => 'bg-success',
                                        'declined' => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp

                                <span class="badge {{ $badge }}">
                                    {{ ucfirst($status) }}
                                </span>

                            </td>

                            {{-- Actions --}}
                            <td>

                                <a href="{{ route('jobs.offers.edit', $offer['id']) }}"
                                    class="btn btn-sm btn-info rounded-pill text-white">
                                    Edit
                                </a>

                                <a href="{{ route('jobs.offers.show', $offer['id']) }}"
                                    class="btn btn-sm btn-info rounded-pill text-white">
                                    View
                                </a>

                                <button type="button" class="btn btn-sm btn-danger rounded-pill">
                                    Delete
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">

                                No job offers found.

                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
</div>
