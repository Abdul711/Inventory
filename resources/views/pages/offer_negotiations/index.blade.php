<?php

use Livewire\Component;
use App\Models\JobOfferNegotiation;

new class extends Component {
    public string $search = '';

    public array $negotiations = [];

    public function mount(): void
    {
        $this->loadNegotiations();
    }

    public function updatedSearch(): void
    {
        $this->loadNegotiations();
    }

    public function loadNegotiations(): void
    {
        $this->negotiations = JobOfferNegotiation::query()
            ->with(['jobOffer', 'applicant', 'proposedBy'])
            ->when($this->search, function ($query) {
                $query
                    ->whereHas('applicant', function ($q) {
                        $q->where('full_name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('jobOffer', function ($q) {
                        $q->where('offer_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('status', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get()
            ->map(function ($negotiation) {
                return [
                    'id' => $negotiation->id,

                    'job_offer_id' => $negotiation->job_offer_id,

                    'offer_number' => $negotiation->jobOffer?->offer_number ?? 'N/A',

                    'applicant_id' => $negotiation->applicant_id,

                    'applicant_name' => $negotiation->applicant?->full_name ?? 'N/A',

                    'applicant_email' => $negotiation->applicant?->email ?? 'N/A',

                    'proposed_by' => $negotiation->proposedBy?->name ?? 'N/A',

                    'proposed_salary' => $negotiation->proposed_salary,

                    'remarks' => $negotiation->remarks ?? 'No remarks',

                    'status' => $negotiation->status,

                    'created_at' => $negotiation->created_at?->format('d M Y h:i A'),
                ];
            })
            ->toArray();
    }
};

?>

<div>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Job Offer Negotiations
            </h3>

            <p class="text-muted mb-0">
                Manage candidate salary negotiations
            </p>

        </div>

        <a href="{{ route('jobs.offer-negotiations.create') }}" class="btn btn-primary rounded-pill">
            Add Negotiation
        </a>

    </div>


    <div class="dashboard-card">

        <input type="text" wire:model.live="search" class="form-control rounded-4 mb-4"
            placeholder="Search applicant, offer number or status...">


        <div class="table-responsive">

            <table class="table align-middle">

                <thead class="table-light">

                    <tr>

                        <th>ID</th>

                        <th>Offer Number</th>

                        <th>Applicant</th>

                        <th>Proposed By</th>

                        <th>Proposed Salary</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse ($negotiations as $negotiation)
                        <tr>

                            <td>
                                #{{ $negotiation['id'] }}
                            </td>


                            <td>

                                <strong>
                                    {{ $negotiation['offer_number'] }}
                                </strong>

                            </td>


                            <td>

                                <div class="fw-semibold">

                                    {{ $negotiation['applicant_name'] }}

                                </div>

                                <small class="text-muted">

                                    {{ $negotiation['applicant_email'] }}

                                </small>

                            </td>


                            <td>

                                {{ $negotiation['proposed_by'] }}

                            </td>


                            <td>

                                Rs.
                                {{ number_format($negotiation['proposed_salary'], 2) }}

                            </td>


                            <td>

                                @if ($negotiation['status'] === 'accepted')
                                    <span class="badge bg-success">
                                        Accepted
                                    </span>
                                @elseif ($negotiation['status'] === 'rejected')
                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>
                                @elseif ($negotiation['status'] === 'countered')
                                    <span class="badge bg-info">
                                        Countered
                                    </span>
                                @elseif ($negotiation['status'] === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                                @else
                                    <span class="badge bg-secondary">

                                        {{ ucfirst($negotiation['status'] ?? 'Unknown') }}

                                    </span>
                                @endif

                            </td>


                            <td>

                                {{ $negotiation['created_at'] }}

                            </td>


                            <td>

                                <a href="{{ route('jobs.offer-negotiations.edit', $negotiation['id']) }}"
                                    class="btn btn-sm btn-info rounded-pill text-white">
                                    Edit
                                </a>


                                <a href="{{ route('jobs.offer-negotiations.show', $negotiation['id']) }}"
                                    class="btn btn-sm btn-primary rounded-pill">
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center text-muted py-4">
                                No job offer negotiations found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
