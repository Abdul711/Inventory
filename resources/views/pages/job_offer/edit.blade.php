<?php

use App\Models\JobOffer;
use App\Models\User;
use Livewire\Component;
use Illuminate\Validation\Rule;

new class extends Component {
    public $job_offer_id;

    public $salary_proposed;
    public $approved_salary;
    public $candidate_expected_salary;

    public $candidate_negotiation_note = '';

    public array $terms_conditions = [''];

    public $negotiated_at;

    public $probation_months = 3;
    public $notice_period_days = 30;

    public array $working_days = [];

    public $status = 'pending';

    public $offer_date;
    public $expiry_date;
    public $contract_start_date;

    public $duty_durations;

    public $approved_by = null;

    public $contact_person = '';
    public $contact_email = '';

    public array $benefits = [''];

    public $additional_notes = '';

    public $withdrawal_reason = '';

    public array $approvers = [];

    public array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount($id): void
    {
        $jobOffer = JobOffer::findOrFail($id);

        $this->job_offer_id = $jobOffer->id;

        $this->salary_proposed = $jobOffer->salary_proposed;

        $this->approved_salary = $jobOffer->approved_salary;

        $this->candidate_expected_salary = $jobOffer->candidate_expected_salary;

        $this->candidate_negotiation_note = $jobOffer->candidate_negotiation_note ?? '';

        /*
        |--------------------------------------------------------------------------
        | Auto Fill Working Days
        |--------------------------------------------------------------------------
        */

        $this->working_days = $this->normalizeArrayValue($jobOffer->getRawOriginal('working_days'));

        /*
        |--------------------------------------------------------------------------
        | Auto Fill Terms & Conditions
        |--------------------------------------------------------------------------
        */

        $this->terms_conditions = $this->normalizeArrayValue($jobOffer->getRawOriginal('terms_conditions'));

        if (empty($this->terms_conditions)) {
            $this->terms_conditions = [''];
        }

        /*
        |--------------------------------------------------------------------------
        | Auto Fill Benefits
        |--------------------------------------------------------------------------
        */

        $this->benefits = $this->normalizeArrayValue($jobOffer->getRawOriginal('benefits'));

        if (empty($this->benefits)) {
            $this->benefits = [''];
        }

        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        $this->negotiated_at = $jobOffer->negotiated_at ? $jobOffer->negotiated_at->format('Y-m-d\TH:i') : null;

        $this->offer_date = $jobOffer->offer_date ? $jobOffer->offer_date->format('Y-m-d') : null;

        $this->expiry_date = $jobOffer->expiry_date ? $jobOffer->expiry_date->format('Y-m-d') : null;

        $this->contract_start_date = $jobOffer->contract_start_date ? $jobOffer->contract_start_date->format('Y-m-d') : null;

        /*
        |--------------------------------------------------------------------------
        | Other Fields
        |--------------------------------------------------------------------------
        */

        $this->probation_months = $jobOffer->probation_months;

        $this->notice_period_days = $jobOffer->notice_period_days;

        $this->duty_durations = $jobOffer->duty_durations;

        $this->status = $jobOffer->status ?? 'pending';

        $this->approved_by = $jobOffer->approved_by;

        $this->contact_person = $jobOffer->contact_person ?? '';

        $this->contact_email = $jobOffer->contact_email ?? '';

        $this->additional_notes = $jobOffer->additional_notes ?? '';

        $this->withdrawal_reason = $jobOffer->withdrawal_reason ?? '';

        /*
        |--------------------------------------------------------------------------
        | Approvers
        |--------------------------------------------------------------------------
        */

        $this->approvers = User::select('id', 'name')->orderBy('name')->get()->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize JSON / Comma Separated / Old Data
    |--------------------------------------------------------------------------
    */

    private function normalizeArrayValue(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Already Array
        |--------------------------------------------------------------------------
        */

        if (is_array($value)) {
            return array_values(array_filter(array_map(fn($item) => trim((string) $item), $value), fn($item) => $item !== ''));
        }

        /*
        |--------------------------------------------------------------------------
        | Try JSON Decode
        |--------------------------------------------------------------------------
        */

        $decoded = json_decode($value, true);

        /*
         * Example:
         *
         * ["Monday","Tuesday"]
         */

        if (is_array($decoded)) {
            return array_values(array_filter(array_map(fn($item) => trim((string) $item), $decoded), fn($item) => $item !== ''));
        }

        /*
         * Example:
         *
         * "Monday,Tuesday,Wednesday"
         *
         * JSON decoded result becomes string.
         */

        if (is_string($decoded)) {
            $value = $decoded;
        }

        /*
        |--------------------------------------------------------------------------
        | Check New Line Data
        |--------------------------------------------------------------------------
        */

        if (str_contains($value, "\n") || str_contains($value, "\r")) {
            $items = preg_split('/\r\n|\r|\n/', $value);
        } else {
            /*
            |--------------------------------------------------------------------------
            | Old Comma Separated Data
            |--------------------------------------------------------------------------
            */

            $items = explode(',', $value);
        }

        return array_values(array_filter(array_map(fn($item) => trim((string) $item), $items), fn($item) => $item !== ''));
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [
            'salary_proposed' => ['required', 'numeric', 'min:0'],

            'approved_salary' => ['nullable', 'numeric', 'min:0'],

            'candidate_expected_salary' => ['nullable', 'numeric', 'min:0'],

            'candidate_negotiation_note' => ['nullable', 'string', 'max:2000'],

            /*
            |--------------------------------------------------------------------------
            | Terms & Conditions
            |--------------------------------------------------------------------------
            */

            'terms_conditions' => ['nullable', 'array'],

            'terms_conditions.*' => ['nullable', 'string', 'max:1000'],

            /*
            |--------------------------------------------------------------------------
            | Working Days
            |--------------------------------------------------------------------------
            */

            'working_days' => ['required', 'array', 'min:1'],

            'working_days.*' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],

            /*
            |--------------------------------------------------------------------------
            | Benefits
            |--------------------------------------------------------------------------
            */

            'benefits' => ['nullable', 'array'],

            'benefits.*' => ['nullable', 'string', 'max:500'],

            /*
            |--------------------------------------------------------------------------
            | Employment
            |--------------------------------------------------------------------------
            */

            'probation_months' => ['required', 'integer', 'min:0', 'max:24'],

            'notice_period_days' => ['required', 'integer', 'min:0', 'max:365'],

            'duty_durations' => ['nullable', 'numeric', 'min:0', 'max:24'],

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'offer_date' => ['required', 'date'],

            'expiry_date' => ['nullable', 'date', 'after_or_equal:offer_date'],

            'contract_start_date' => ['nullable', 'date'],

            'negotiated_at' => ['nullable', 'date'],

            /*
            |--------------------------------------------------------------------------
            | Contact / Approval
            |--------------------------------------------------------------------------
            */

            'approved_by' => ['nullable', 'exists:users,id'],

            'contact_person' => ['nullable', 'string', 'max:255'],

            'contact_email' => ['nullable', 'email', 'max:255'],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => ['required', Rule::in(['pending', 'accepted', 'declined'])],

            'additional_notes' => ['nullable', 'string', 'max:5000'],

            'withdrawal_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected $messages = [
        'salary_proposed.required' => 'Proposed salary is required.',

        'salary_proposed.numeric' => 'Proposed salary must be numeric.',

        'approved_salary.numeric' => 'Approved salary must be numeric.',

        'candidate_expected_salary.numeric' => 'Candidate expected salary must be numeric.',

        'working_days.required' => 'Please select at least one working day.',

        'working_days.min' => 'Please select at least one working day.',

        'offer_date.required' => 'Offer date is required.',

        'expiry_date.after_or_equal' => 'Expiry date must be equal to or after the offer date.',

        'contact_email.email' => 'Please enter a valid contact email.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Live Validation
    |--------------------------------------------------------------------------
    */

    public function updated($property): void
    {
        $this->validateOnly($property);
    }

    /*
    |--------------------------------------------------------------------------
    | Terms & Conditions
    |--------------------------------------------------------------------------
    */

    public function addTerm(): void
    {
        $this->terms_conditions[] = '';
    }

    public function removeTerm($index): void
    {
        unset($this->terms_conditions[$index]);

        $this->terms_conditions = array_values($this->terms_conditions);

        if (empty($this->terms_conditions)) {
            $this->terms_conditions = [''];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Benefits
    |--------------------------------------------------------------------------
    */

    public function addBenefit(): void
    {
        $this->benefits[] = '';
    }

    public function removeBenefit($index): void
    {
        unset($this->benefits[$index]);

        $this->benefits = array_values($this->benefits);

        if (empty($this->benefits)) {
            $this->benefits = [''];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        $this->validate();

        $jobOffer = JobOffer::findOrFail($this->job_offer_id);

        /*
        |--------------------------------------------------------------------------
        | Clean Terms
        |--------------------------------------------------------------------------
        */

        $terms = array_values(array_filter(array_map(fn($term) => trim((string) $term), $this->terms_conditions), fn($term) => $term !== ''));

        /*
        |--------------------------------------------------------------------------
        | Clean Benefits
        |--------------------------------------------------------------------------
        */

        $benefits = array_values(array_filter(array_map(fn($benefit) => trim((string) $benefit), $this->benefits), fn($benefit) => $benefit !== ''));

        /*
        |--------------------------------------------------------------------------
        | Update Job Offer
        |--------------------------------------------------------------------------
        */

        $jobOffer->update([
            'salary_proposed' => $this->salary_proposed,

            'approved_salary' => $this->approved_salary ?: null,

            'candidate_expected_salary' => $this->candidate_expected_salary ?: null,

            'candidate_negotiation_note' => $this->candidate_negotiation_note ?: null,

            /*
            |--------------------------------------------------------------------------
            | Arrays
            |--------------------------------------------------------------------------
            */

            'terms_conditions' => $terms,

            'benefits' => $benefits,

            'working_days' => array_values($this->working_days),

            /*
            |--------------------------------------------------------------------------
            | Employment
            |--------------------------------------------------------------------------
            */

            'probation_months' => $this->probation_months,

            'notice_period_days' => $this->notice_period_days,

            'duty_durations' => $this->duty_durations ?: null,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'offer_date' => $this->offer_date,

            'expiry_date' => $this->expiry_date ?: null,

            'contract_start_date' => $this->contract_start_date ?: null,

            'negotiated_at' => $this->negotiated_at ?: null,

            /*
            |--------------------------------------------------------------------------
            | Approval / Contact
            |--------------------------------------------------------------------------
            */

            'approved_by' => $this->approved_by ?: null,

            'contact_person' => $this->contact_person ?: null,

            'contact_email' => $this->contact_email ?: null,

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'status' => $this->status,

            'additional_notes' => $this->additional_notes ?: null,

            'withdrawal_reason' => $this->withdrawal_reason ?: null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reload Array Values
        |--------------------------------------------------------------------------
        |
        | This makes sure the current Livewire state contains
        | clean values after update.
        |
        */

        $this->working_days = array_values($this->working_days);

        $this->terms_conditions = !empty($terms) ? $terms : [''];

        $this->benefits = !empty($benefits) ? $benefits : [''];

        session()->flash('success', 'Job offer updated successfully.');
    }
};

?>

<div class="row">

    <div class="col-lg-12">

        <div class="card shadow border-0">

            {{-- Header --}}
            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Edit Job Offer
                </h4>

            </div>


            <div class="card-body">

                {{-- Success --}}
                @if (session()->has('success'))
                    <div class="alert alert-success">

                        {{ session('success') }}

                    </div>
                @endif


                <form wire:submit="update">

                    {{-- ===================================================== --}}
                    {{-- Salary --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Salary Information
                    </h5>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Proposed Salary
                            </label>

                            <input type="number" step="0.01" placeholder="Enter proposed salary"
                                wire:model.live="salary_proposed"
                                class="form-control
                                    @error('salary_proposed')
                                        is-invalid
                                    @enderror">

                            @error('salary_proposed')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Approved Salary
                            </label>

                            <input type="number" step="0.01" placeholder="Enter approved salary"
                                wire:model.live="approved_salary"
                                class="form-control
                                    @error('approved_salary')
                                        is-invalid
                                    @enderror">

                            @error('approved_salary')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Candidate Expected Salary
                            </label>

                            <input type="number" step="0.01" placeholder="Enter expected salary"
                                wire:model.live="candidate_expected_salary"
                                class="form-control
                                    @error('candidate_expected_salary')
                                        is-invalid
                                    @enderror">

                            @error('candidate_expected_salary')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Offer Dates --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Offer Dates
                    </h5>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Offer Date
                            </label>

                            <input type="date" wire:model.live="offer_date"
                                class="form-control
                                    @error('offer_date')
                                        is-invalid
                                    @enderror">

                            @error('offer_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Expiry Date
                            </label>

                            <input type="date" wire:model.live="expiry_date"
                                class="form-control
                                    @error('expiry_date')
                                        is-invalid
                                    @enderror">

                            @error('expiry_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Contract Start Date
                            </label>

                            <input type="date" wire:model.live="contract_start_date"
                                class="form-control
                                    @error('contract_start_date')
                                        is-invalid
                                    @enderror">

                            @error('contract_start_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Employment Conditions --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Employment Conditions
                    </h5>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Probation Months
                            </label>

                            <input type="number" wire:model.live="probation_months"
                                class="form-control
                                    @error('probation_months')
                                        is-invalid
                                    @enderror">

                            @error('probation_months')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Notice Period Days
                            </label>

                            <input type="number" wire:model.live="notice_period_days"
                                class="form-control
                                    @error('notice_period_days')
                                        is-invalid
                                    @enderror">

                            @error('notice_period_days')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Duty Duration
                            </label>

                            <input type="number" step="0.01" wire:model.live="duty_durations"
                                class="form-control
                                    @error('duty_durations')
                                        is-invalid
                                    @enderror">

                            @error('duty_durations')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Working Days --}}
                    {{-- ===================================================== --}}

                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            Working Days
                        </label>

                        <div class="border rounded p-3">

                            <div class="row">

                                @foreach ($days as $day)
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2"
                                        wire:key="working-day-{{ $day }}">

                                        <div class="form-check">

                                            <input type="checkbox" id="working_day_{{ strtolower($day) }}"
                                                value="{{ $day }}" wire:model.live="working_days"
                                                class="form-check-input">

                                            <label class="form-check-label" for="working_day_{{ strtolower($day) }}">
                                                {{ $day }}
                                            </label>

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                        </div>


                        @error('working_days')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                        @enderror


                        @error('working_days.*')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                        @enderror


                        @if (!empty($working_days))

                            <div class="mt-2">

                                <span class="text-muted small">
                                    Selected:
                                </span>

                                @foreach ($working_days as $day)
                                    <span class="badge bg-primary me-1">

                                        {{ $day }}

                                    </span>
                                @endforeach

                            </div>

                        @endif

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Terms & Conditions --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Terms & Conditions
                    </h5>

                    <div class="mb-4">

                        @foreach ($terms_conditions as $index => $term)
                            <div class="mb-2" wire:key="term-{{ $index }}">

                                <div class="input-group">

                                    <input type="text" placeholder="Enter term or condition"
                                        wire:model.live="terms_conditions.{{ $index }}"
                                        class="form-control
                                            @error('terms_conditions.' . $index)
                                                is-invalid
                                            @enderror">

                                    @if (count($terms_conditions) > 1)
                                        <button type="button" wire:click="removeTerm({{ $index }})"
                                            class="btn btn-outline-danger">

                                            <i class="bi bi-trash"></i>

                                            Remove

                                        </button>
                                    @endif

                                </div>


                                @error('terms_conditions.' . $index)
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>
                        @endforeach


                        <button type="button" wire:click="addTerm" class="btn btn-outline-primary btn-sm">

                            <i class="bi bi-plus-circle me-1"></i>

                            Add Term

                        </button>

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Benefits --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Benefits
                    </h5>

                    <div class="mb-4">

                        @foreach ($benefits as $index => $benefit)
                            <div class="mb-2" wire:key="benefit-{{ $index }}">

                                <div class="input-group">

                                    <input type="text" placeholder="Enter benefit"
                                        wire:model.live="benefits.{{ $index }}"
                                        class="form-control
                                            @error('benefits.' . $index)
                                                is-invalid
                                            @enderror">

                                    @if (count($benefits) > 1)
                                        <button type="button" wire:click="removeBenefit({{ $index }})"
                                            class="btn btn-outline-danger">

                                            <i class="bi bi-trash"></i>

                                            Remove

                                        </button>
                                    @endif

                                </div>


                                @error('benefits.' . $index)
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>
                        @endforeach


                        <button type="button" wire:click="addBenefit" class="btn btn-outline-primary btn-sm">

                            <i class="bi bi-plus-circle me-1"></i>

                            Add Benefit

                        </button>

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Contact Information --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Contact Information
                    </h5>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Contact Person
                            </label>

                            <input type="text" placeholder="Enter contact person" wire:model.live="contact_person"
                                class="form-control
                                    @error('contact_person')
                                        is-invalid
                                    @enderror">

                            @error('contact_person')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Contact Email
                            </label>

                            <input type="email" placeholder="Enter contact email" wire:model.live="contact_email"
                                class="form-control
                                    @error('contact_email')
                                        is-invalid
                                    @enderror">

                            @error('contact_email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Approved By --}}
                    {{-- ===================================================== --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Approved By
                        </label>

                        <select wire:model.live="approved_by"
                            class="form-select
                                @error('approved_by')
                                    is-invalid
                                @enderror">

                            <option value="">
                                Select Approver
                            </option>

                            @foreach ($approvers as $approver)
                                <option value="{{ $approver['id'] }}">

                                    {{ $approver['name'] }}

                                </option>
                            @endforeach

                        </select>


                        @error('approved_by')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <hr>


                    {{-- ===================================================== --}}
                    {{-- Negotiation --}}
                    {{-- ===================================================== --}}

                    <h5 class="fw-bold mb-3">
                        Negotiation
                    </h5>

                    <div class="row">

                        <div class="col-md-8 mb-3">

                            <label class="form-label">
                                Candidate Negotiation Note
                            </label>

                            <textarea rows="4" placeholder="Enter candidate negotiation note" wire:model.live="candidate_negotiation_note"
                                class="form-control
                                    @error('candidate_negotiation_note')
                                        is-invalid
                                    @enderror"></textarea>

                            @error('candidate_negotiation_note')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Negotiated At
                            </label>

                            <input type="datetime-local" wire:model.live="negotiated_at"
                                class="form-control
                                    @error('negotiated_at')
                                        is-invalid
                                    @enderror">

                            @error('negotiated_at')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Additional Notes --}}
                    {{-- ===================================================== --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Additional Notes
                        </label>

                        <textarea rows="4" placeholder="Enter additional notes" wire:model.live="additional_notes"
                            class="form-control
                                @error('additional_notes')
                                    is-invalid
                                @enderror"></textarea>

                        @error('additional_notes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Status --}}
                    {{-- ===================================================== --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Offer Status
                        </label>

                        <select wire:model.live="status"
                            class="form-select
                                @error('status')
                                    is-invalid
                                @enderror">

                            <option value="pending">
                                Pending
                            </option>

                            <option value="accepted">
                                Accepted
                            </option>

                            <option value="declined">
                                Declined
                            </option>

                        </select>


                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Decline Reason --}}
                    {{-- ===================================================== --}}

                    @if ($status === 'declined')
                        <div class="mb-3">

                            <label class="form-label">
                                Withdrawal / Decline Reason
                            </label>

                            <textarea rows="3" placeholder="Enter decline reason" wire:model.live="withdrawal_reason"
                                class="form-control
                                    @error('withdrawal_reason')
                                        is-invalid
                                    @enderror"></textarea>

                            @error('withdrawal_reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                    @endif


                    {{-- ===================================================== --}}
                    {{-- Submit --}}
                    {{-- ===================================================== --}}

                    <div class="d-flex justify-content-end mt-4">

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="update">

                            <span wire:loading.remove wire:target="update">
                                Update Job Offer
                            </span>

                            <span wire:loading wire:target="update">
                                Updating...
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
