<?php

use Livewire\Component;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public int $id;
    public array $working_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    public $application;
    public string $status = '';
    public string $currentStatus = '';
    public ?int $interviewer_id = null;
    public ?string $scheduled_at = null;
    public string $type = '';
    public string $mode = '';
    public ?string $meeting_link = null;
    public $shifts = [];
    public $interviewers = [];
    public $terms = [];
    public $benefits = [];
    public $screened_by = null;
    public $cv_score = null;
    public $experience_score = null;
    public $education_score = null;
    public $overall_score = null;
    public $strengths = '';
    public $weaknesses = '';
    public $remarks = '';
    public $screeners = [];
    public $contact_person;
    public $contact_email;
    public $proposedSalary;
    public $duty_durations;
    public $contract_start_date;
    public $probation_month;
    public $notice_period;
    public $password;
    public function addTerm()
    {
        $this->terms[] = [];
    }
    public function addBenefit()
    {
        $this->benefits[] = [];
    }
    public function removeTerm($index): void
    {
        unset($this->terms[$index]);

        $this->terms = array_values($this->terms);
    }
    public function removeBenefit($index): void
    {
        unset($this->benefits[$index]);

        $this->benefits = array_values($this->benefits);
    }
    public function mount(int $id): void
    {
        $this->id = $id;
        $this->shifts = \App\Models\Shift::get();
        $this->application = JobApplication::with(['applicant', 'jobPosting.designation'])->findOrFail($id);

        $this->status = $this->application->status;
        $this->currentStatus = $this->application->status;
        $this->interviewers = User::select('id', 'name', 'email')->get();
        $this->screeners = User::whereIn('role_id', [1, 3])
            ->select('id', 'name', 'email')
            ->get();
        $interview = Interview::where('job_application_id', $this->application->id)->first();
        $this->terms[] = [];
        $this->benefits[] = [];
        if ($this->application->screening) {
            $this->cv_score = $this->application->screening->cv_score;
            $this->education_score = $this->application->screening->education_score;
            $this->experience_score = $this->application->screening->experience_score;
            $this->overall_score = $this->application->screening->overall_score;
            $this->screened_by = $this->application->screening->screened_by;
        }
        if ($this->application->offer) {
            // dd($this->application->offer->terms_conditions);
            if (!empty($this->application->offer->terms_conditions)) {
                $this->terms = $this->application->offer->terms_conditions;
            }
            $this->benefits = $this->application->offer->benefits;
            $this->contact_person = $this->application->offer->contact_person;
            $this->contact_email = $this->application->offer->contact_email;
            $this->proposedSalary = $this->application->offer->salary_proposed;
            $this->duty_durations = $this->application->offer->duty_durations;
            $this->contract_start_date = date('Y-m-d', strtotime($this->application->offer->contract_start_date));
            $this->probation_month = $this->application->offer->probation_months;
            $this->notice_period = $this->application->offer->notice_period_days;
            $this->working_days = explode(',', $this->application->offer->working_days);
        }
        if ($interview) {
            $this->interviewer_id = $interview->interviewer_id;

            $this->scheduled_at = $interview->scheduled_at?->format('Y-m-d\TH:i');

            $this->type = $interview->type ?? '';

            $this->mode = $interview->mode ?? '';

            $this->meeting_link = $interview->meeting_link;
        }
    }

    public function updatedStatus($value): void
    {
        if ($value !== 'interview') {
            $this->reset(['interviewer_id', 'scheduled_at', 'type', 'mode', 'meeting_link']);
        }
    }

    public function updatedMode($value): void
    {
        if ($value !== 'online') {
            $this->meeting_link = null;
        }
    }

    public function save(): void
    {
        $this->validate([
            'status' => ['required', 'in:pending,shortlisted,interview,rejected,hired,screening,job_offered'],
            'interviewer_id' => ['nullable', 'required_if:status,interview', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'required_if:status,interview', 'date'],
            'type' => ['nullable', 'required_if:status,interview', 'in:hr,technical'],
            'mode' => ['nullable', 'required_if:status,interview', 'in:online,physical,phone'],
            'meeting_link' => ['nullable', 'required_if:mode,online', 'regex:/\Ahttps:\/\/meet\.google\.com\/[a-z]{3}-[a-z]{4}-[a-z]{3}\z/'],
            'screened_by' => ['nullable', 'required_if:status,screening', 'exists:users,id'],
            'cv_score' => ['nullable', 'required_if:status,screening', 'integer', 'between:1,5'],
            'experience_score' => 'nullable|required_if:status,screening|integer|between:1,5',
            'education_score' => 'nullable|required_if:status,screening|integer|between:1,5',
            'contact_person' => ['nullable', 'required_if:status,job_offered'],
            'contact_email' => ['nullable', 'required_if:status,job_offered', 'email'],
            'working_days' => ['nullable', 'required_if:status,job_offered'],
            'proposedSalary' => ['nullable', 'required_if:status,job_offered'],
            'duty_durations' => ['nullable', 'required_if:status,job_offered'],
            'contract_start_date' => ['nullable', 'required_if:status,job_offered', 'date', 'after_or_equal:today'],
            'probation_month' => ['nullable', 'required_if:status,job_offered'],
            'notice_period' => ['nullable', 'required_if:status,job_offered'],
            'terms' => ['nullable', 'required_if:status,job_offered'],
        ]);
        $string = $this->strengths;
        $array = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $string)));
        $strength = implode(',', $array);
        $cv_score = $this->cv_score;
        $experience_score = $this->experience_score;
        $education_score = $this->education_score;
        $overall_score = (float) number_format(((float) $cv_score + (float) $experience_score + (float) $education_score) / 3, 2);
        try {
            DB::transaction(function () use ($overall_score, $strength) {
                if ($this->status == 'job_offered') {
                    $candidate_expected_salary = $this->application->expected_salary;
                    $contact_email = $this->contact_email;
                    $contact_person = $this->contact_person;
                    $working_days = $this->working_days;
                    $applicant_id = $this->application->applicant_id;
                    $terms_conditions = $this->terms;
                    $benefits = $this->benefits;
                    $proposedSalary = $this->proposedSalary;
                    $duty_durations = $this->duty_durations;
                    $contract_start_date = $this->contract_start_date;
                    $probation_month = $this->probation_month;
                    $notice_period = $this->notice_period;
                    $offer = $this->application->offer()->updateOrCreate(
                        [
                            'applicant_id' => $this->application->applicant_id,
                            'job_application_id' => $this->application->id,
                        ],
                        [
                            'terms_conditions' => $terms_conditions,
                            'candidate_expected_salary' => $this->application->expected_salary,
                            'contact_email' => $contact_email,
                            'contact_person' => $contact_person,
                            'working_days' => implode(',', $working_days),
                            'benefits' => $this->benefits,
                            'salary_proposed' => $proposedSalary,
                            'duty_durations' => $duty_durations,
                            'contract_start_date' => $contract_start_date,
                            'probation_months' => $probation_month,
                            'notice_period_days' => $notice_period,
                            'expiry_date' => date('Y-m-d', strtotime('+1 month')),
                            'offer_date' => date('Y-m-d'),
                            'created_by' => auth()->user()->id,
                        ],
                    );
                }

                if ($this->status == 'screening') {
                    \App\Models\Screening::updateOrCreate(
                        [
                            'job_application_id' => $this->application->id,
                        ],
                        [
                            'screened_by' => $this->screened_by,
                            'cv_score' => $this->cv_score,
                            'experience_score' => $this->experience_score,
                            'education_score' => $this->education_score,
                            'overall_score' => $this->overall_score,
                            'strengths' => $strength,
                            'remarks' => $this->remarks,
                            'screened_at' => date('Y-m-d'),
                        ],
                    );
                }
                $this->application->update([
                    'status' => $this->status,
                ]);

                if ($this->status === 'interview') {
                    Interview::updateOrCreate(
                        [
                            'job_application_id' => $this->application->id,
                        ],
                        [
                            'applicant_id' => $this->application->applicant_id,
                            'interviewer_id' => $this->interviewer_id,
                            'scheduled_at' => $this->scheduled_at,
                            'type' => $this->type,
                            'mode' => $this->mode,
                            'meeting_link' => $this->mode === 'online' ? $this->meeting_link : null,
                        ],
                    );
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
        session()->flash('success', 'Job application updated successfully.');
    }
};
?>
<div class="row justify-content-center">

    <div class="col-lg-12 col-xl-12">

        <div class="card shadow border-0">

            {{-- Header --}}
            <div class="card-header bg-primary text-white py-3">

                <h4 class="mb-0">
                    Update Job Application
                </h4>

            </div>

            <div class="card-body p-4">

                {{-- Success Message --}}
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif


                {{-- Application Information --}}
                <div class="row g-3 mb-4">

                    {{-- Applicant --}}
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Applicant
                        </label>

                        <input type="text" class="form-control" value="{{ $application->applicant->full_name }}"
                            disabled>

                    </div>


                    {{-- Applied Job --}}
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Applied Job
                        </label>

                        <input type="text" class="form-control"
                            value="{{ $application->jobPosting->designation->name }}" disabled>

                    </div>

                </div>


                <form wire:submit="save">

                    {{-- Status --}}
                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Application Status
                            </label>

                            <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror">

                                <option value="">
                                    Select Status
                                </option>

                                <option value="pending">
                                    Pending
                                </option>
                                <option value="rejected">
                                    Rejected
                                </option>
                                <option value="screening">
                                    Screening
                                </option>
                                <option value="shortlisted" @disabled(!in_array($currentStatus, ['screening']))>
                                    Shortlisted
                                </option>

                                <option value="interview" @disabled(!in_array($currentStatus, ['shortlisted']))>
                                    Interview
                                </option>




                                <option value="job_offered" @disabled(!in_array($currentStatus, ['interview']))>
                                    Job Offer
                                </option>


                                <option value="hired" @disabled(!in_array($currentStatus, ['job_offered']))>
                                    Hired
                                </option>

                            </select>

                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    @if ($status === 'interview')

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3">
                            Interview Details
                        </h5>


                        {{-- Interviewer + Date --}}
                        <div class="row g-3 mb-3">

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Interviewer
                                </label>

                                <select wire:model="interviewer_id"
                                    class="form-select
                                    @error('interviewer_id') is-invalid @enderror">

                                    <option value="">
                                        Select Interviewer
                                    </option>

                                    @foreach ($interviewers as $interviewer)
                                        <option value="{{ $interviewer->id }}">

                                            {{ $interviewer->name }}

                                            @if ($interviewer->email)
                                                - {{ $interviewer->email }}
                                            @endif

                                        </option>
                                    @endforeach

                                </select>

                                @error('interviewer_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Interview Date & Time
                                </label>

                                <input type="datetime-local" wire:model="scheduled_at"
                                    class="form-control
                                    @error('scheduled_at') is-invalid @enderror">

                                @error('scheduled_at')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Mode + Type --}}
                        <div class="row g-3 mb-3">

                            {{-- Mode --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Interview Mode
                                </label>

                                <select wire:model.live="mode"
                                    class="form-select
                                    @error('mode') is-invalid @enderror">

                                    <option value="">
                                        Select Interview Mode
                                    </option>

                                    <option value="online">
                                        Online
                                    </option>

                                    <option value="physical">
                                        Physical
                                    </option>

                                    <option value="phone">
                                        Phone
                                    </option>

                                </select>

                                @error('mode')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- Type --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Interview Type
                                </label>

                                <select wire:model="type"
                                    class="form-select
                                    @error('type') is-invalid @enderror">

                                    <option value="">
                                        Select Interview Type
                                    </option>

                                    <option value="technical">
                                        Technical
                                    </option>

                                    <option value="hr">
                                        HR
                                    </option>

                                </select>

                                @error('type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Meeting Link --}}
                        @if ($mode === 'online')
                            <div class="row g-3 mb-3">

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">
                                        Google Meet Link
                                    </label>

                                    <input type="url" wire:model="meeting_link"
                                        class="form-control
                                        @error('meeting_link') is-invalid @enderror"
                                        placeholder="https://meet.google.com/abc-defg-hij">

                                    @error('meeting_link')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>
                        @endif

                    @endif
                    @if ($status === 'job_offered')
                        <hr class="my-4">
                        <h5 class="fw-bold mb-3">
                            Job Offer Details
                        </h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Expected Salary
                                </label>

                                <input type="text" class="form-control" value="{{ $application->expected_salary }}"
                                    disabled>

                            </div>
                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Current Salary
                                </label>

                                <input type="text" class="form-control" value="{{ $application->current_salary }}"
                                    disabled>

                            </div>
                            <div class="col-md-6">

                                <label class="form-label fw-bold">
                                    Minimum Salary For this Post
                                </label>

                                <input type="text" class="form-control"
                                    value="{{ $application->jobPosting->minimum_salary }}" disabled>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Contact Person
                                </label>
                                <input type="text" class="form-control" wire:model='contact_person'
                                    placeholder="Contact Person Name">

                                @error('contact_person')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror


                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Contact Email
                                </label>
                                <input type="text" class="form-control" wire:model='contact_email'
                                    placeholder="Contact Person Name">

                                @error('contact_email')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            @php
                                $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
                            @endphp

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Working Days
                                </label>

                                @for ($i = 0; $i < 7; $i++)
                                    @php
                                        $day = $startOfWeek->copy()->addDays($i)->format('l');
                                    @endphp

                                    <div class="col-md-12 col-sm-12 mb-2">
                                        <div class="form-check">

                                            <input class="form-check-input" type="checkbox" wire:model="working_days"
                                                value="{{ $day }}" id="day_{{ strtolower($day) }}">

                                            <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                                {{ $day }}
                                            </label>

                                        </div>
                                    </div>
                                @endfor

                                @error('working_days')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Duty Duration
                                </label>
                                <input type="text" class="form-control" wire:model="duty_durations"
                                    placeholder="Duty Duration">
                                @error('duty_durations')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <div class="col-md-6">

                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-semibold">Terms & Conditions</label>
                                    <div class="row">
                                        @foreach ($terms as $index => $term)
                                            <div class="col-md-10">
                                                <textarea wire:model.live="terms.{{ $index }}" class="form-control mb-1" placeholder="Term & Condition"></textarea>
                                            </div>
                                            @if ($index > 0)
                                                <div class="col-md-2">
                                                    <button class="btn btn-danger"
                                                        wire:click.prevent="removeTerm({{ $index }})">Remove</button>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                </div>
                                <button type="button" wire:click.prevent="addTerm"
                                    class="btn btn-primary rounded-pill">
                                    <i class="bi bi-plus-circle me-2"></i>Add Term
                                </button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Salary Propose
                                </label>
                                <input type="text" class="form-control" wire:model="proposedSalary"
                                    placeholder="Salary Propose">
                                @error('proposedSalary')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Probation Month
                                </label>
                                <input type="text" class="form-control" wire:model="probation_month"
                                    placeholder="Salary Propose">
                                @error('probation_month')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Notice Period
                                </label>
                                <input type="text" class="form-control" wire:model="notice_period"
                                    placeholder="Salary Propose">
                                @error('notice_period')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Contract Start Date
                                </label>
                                <input type="date" class="form-control" min="{{ now()->toDateString() }}"
                                    wire:model="contract_start_date" placeholder="Salary Propose">

                                @error('contract_start_date')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-12">

                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-semibold">Benefits</label>
                                    <div class="row">
                                        @foreach ($benefits as $index => $benefit)
                                            <div class="col-md-10">
                                                <input wire:model.live="benefits.{{ $index }}"
                                                    class="form-control mb-1" placeholder="Benefit">
                                            </div>
                                            @if ($index > 0)
                                                <div class="col-md-2">
                                                    <button class="btn btn-danger"
                                                        wire:click.prevent="removeBenefit({{ $index }})">Remove</button>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                </div>
                                <button type="button" wire:click.prevent="addBenefit"
                                    class="btn btn-primary rounded-pill">
                                    <i class="bi bi-plus-circle me-2"></i>Add Benefit
                                </button>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Additional Notes
                                </label>
                                <textarea class="form-control  placeholder="Additional No">
                                </textarea>
                            </div>

                        </div>
                    @endif
                    @if ($status === 'hired')
                        <hr class="my-4">
                        <h5 class="fw-bold mb-3">
                            Employee Creation
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="shift" class="form-label fw-semibold">
                                    Shift <span class="text-danger">*</span>
                                </label>

                                <select id="shift" class="form-select @error('shift') is-invalid @enderror"
                                    wire:model="shift">
                                    <option value="">Select Shift</option>

                                    @foreach ($shifts as $item)
                                        <option value="{{ $item->id }}">
                                            {{ ucfirst($item->name) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('shift')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Reporting Time --}}
                            <div class="col-md-6 mb-3">
                                <label for="reporting_time" class="form-label fw-semibold">
                                    Reporting Time
                                </label>

                                <input type="time" id="reporting_time" wire:model="reporting_time"
                                    class="form-control @error('reporting_time') is-invalid @enderror">

                                @error('reporting_time')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- Emergency Contact Name --}}
                            <div class="col-md-4">
                                <label for="emergency_contact_name" class="form-label fw-semibold">
                                    Contact Name
                                </label>

                                <input type="text" id="emergency_contact_name" wire:model="emergency_contact_name"
                                    class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                    placeholder="Enter contact name">

                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="password" class="form-label fw-semibold">
                                    Password
                                </label>

                                <input type="password" id="password" wire:model="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter contact name">

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            {{-- Emergency Contact Number --}}
                            <div class="col-md-4">
                                <label for="emergency_contact_number" class="form-label fw-semibold">
                                    Contact Number
                                </label>

                                <input type="tel" id="emergency_contact_number"
                                    wire:model="emergency_contact_number"
                                    class="form-control @error('emergency_contact_number') is-invalid @enderror"
                                    placeholder="e.g. 03001234567">

                                @error('emergency_contact_number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Relationship --}}
                            <div class="col-md-4">
                                <label for="emergency_contact_relationship" class="form-label fw-semibold">
                                    Relationship
                                </label>

                                <select id="emergency_contact_relationship"
                                    wire:model="emergency_contact_relationship"
                                    class="form-select @error('emergency_contact_relationship') is-invalid @enderror">
                                    <option value="">Select Relationship</option>
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="brother">Brother</option>
                                    <option value="sister">Sister</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="friend">Friend</option>
                                    <option value="other">Other</option>
                                </select>

                                @error('emergency_contact_relationship')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bank Name</label>
                                <input type="text" wire:model="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror"
                                    placeholder="Enter bank name">

                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Account Title --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Account Title</label>
                                <input type="text" wire:model="account_title"
                                    class="form-control @error('account_title') is-invalid @enderror"
                                    placeholder="Enter account title">

                                @error('account_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Account Number --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Account Number</label>
                                <input type="text" wire:model="account_number"
                                    class="form-control @error('account_number') is-invalid @enderror"
                                    placeholder="Enter account number">

                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- IBAN --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">IBAN</label>
                                <input type="text" wire:model="iban"
                                    class="form-control @error('iban') is-invalid @enderror"
                                    placeholder="e.g. PK36SCBL0000001123456702">

                                @error('iban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Branch Name --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Branch Name</label>
                                <input type="text" wire:model="branch_name"
                                    class="form-control @error('branch_name') is-invalid @enderror"
                                    placeholder="Enter branch name">

                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Branch Code --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Branch Code</label>
                                <input type="text" wire:model="branch_code"
                                    class="form-control @error('branch_code') is-invalid @enderror"
                                    placeholder="Enter branch code">

                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- SWIFT Code --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SWIFT Code</label>
                                <input type="text" wire:model="swift_code"
                                    class="form-control @error('swift_code') is-invalid @enderror"
                                    placeholder="Enter SWIFT/BIC code">

                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Primary Account --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold d-block">
                                    Primary Account
                                </label>

                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" wire:model="is_primary" class="form-check-input"
                                        id="is_primary">

                                    <label class="form-check-label" for="is_primary">
                                        Set as primary bank account
                                    </label>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea wire:model="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Additional bank account notes"></textarea>

                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>
                    @endif
                    @if ($status === 'screening')
                        <hr class="my-4">
                        <h5 class="fw-bold mb-3">
                            Screening Process
                        </h5>
                        <div class="row g-3">

                            {{-- Screened By --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Screened By <span class="text-danger">*</span>
                                </label>

                                <select wire:model="screened_by"
                                    class="form-select @error('screened_by') is-invalid @enderror">

                                    <option value="">Select User</option>

                                    @foreach ($screeners as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }}
                                            @if ($user->email)
                                                - {{ $user->email }}
                                            @endif
                                        </option>
                                    @endforeach

                                </select>

                                @error('screened_by')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- CV Score --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    CV Score
                                </label>

                                <input type="number" wire:model="cv_score" min="0" max="100"
                                    class="form-control @error('cv_score') is-invalid @enderror"
                                    placeholder="Enter CV score">

                                @error('cv_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Experience Score --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Experience Score
                                </label>

                                <input type="number" wire:model="experience_score" min="0" max="100"
                                    class="form-control @error('experience_score') is-invalid @enderror"
                                    placeholder="Enter experience score">

                                @error('experience_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Education Score --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Education Score
                                </label>

                                <input type="number" wire:model="education_score" min="0" max="100"
                                    class="form-control @error('education_score') is-invalid @enderror"
                                    placeholder="Enter education score">

                                @error('education_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Overall Score --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Overall Score
                                </label>

                                <input type="number" wire:model="overall_score" min="0" max="100"
                                    class="form-control @error('overall_score') is-invalid @enderror"
                                    placeholder="Enter overall score">

                                @error('overall_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Strengths --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Strengths
                                </label>

                                <textarea wire:model="strengths" rows="3" class="form-control @error('strengths') is-invalid @enderror"
                                    placeholder="Enter candidate strengths"></textarea>

                                @error('strengths')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Weaknesses --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Weaknesses
                                </label>

                                <textarea wire:model="weaknesses" rows="3" class="form-control @error('weaknesses') is-invalid @enderror"
                                    placeholder="Enter candidate weaknesses"></textarea>

                                @error('weaknesses')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Remarks
                                </label>

                                <textarea wire:model="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror"
                                    placeholder="Enter screening remarks"></textarea>

                                @error('remarks')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>
                    @endif
                    {{-- Submit --}}
                    <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">

                        <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled"
                            wire:target="save">

                            <span wire:loading.remove wire:target="save">
                                Update Application
                            </span>

                            <span wire:loading wire:target="save">
                                Updating...
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
