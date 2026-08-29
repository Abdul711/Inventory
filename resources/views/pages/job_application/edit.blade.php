<?php

use Livewire\Component;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public int $id;

    public $application;
    public string $status = '';
    public ?int $interviewer_id = null;
    public ?string $scheduled_at = null;
    public string $type = '';
    public string $mode = '';
    public ?string $meeting_link = null;

    public $interviewers = [];

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->application = JobApplication::with(['applicant', 'jobPosting'])->findOrFail($id);

        $this->status = $this->application->status;

        // Change this according to your user/role structure
        $this->interviewers = User::select('id', 'name', 'email')->get();

        $interview = Interview::where('job_application_id', $this->application->id)->first();

        if ($interview) {
            $this->interviewer_id = $interview->interviewer_id;

            $this->scheduled_at = $interview->scheduled_at?->format('Y-m-d\TH:i');

            $this->type = $interview->type;

            $this->meeting_link = $interview->meeting_link;
        }
    }

    public function updatedStatus($value): void
    {
        if ($value !== 'interview') {
            $this->reset(['interviewer_id', 'scheduled_at', 'type', 'meeting_link']);
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
            'status' => ['required', 'in:pending,shortlisted,interview,rejected,hired'],
            'interviewer_id' => ['nullable', 'required_if:status,interview', 'exists:users,id'],
            'scheduled_at' => ['nullable', 'required_if:status,interview', 'date'],
            'type' => ['nullable', 'required_if:status,interview', 'in:online,physical,phone'],
            'meeting_link' => ['nullable', 'required_if:type,online', 
            'regex:/\Ahttps:\/\/meet\.google\.com\/[a-z]{3}-[a-z]{4}-[a-z]{3}\z/',
            
            'url', 'max:255'],
        ]);

        DB::transaction(function () {
            $this->application->update([
                'status' => $this->status,
            ]);
            dd($this->status === 'hired');
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

                        'meeting_link' => $this->type === 'online' ? $this->meeting_link : null,
                    ],
                );
            }
        });

        session()->flash('success', 'Job application updated successfully.');
    }
};

?>
<div class="row">
    <div class="col-lg-12">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    Update Job Application
                </h4>
            </div>

            <div class="card-body">

                {{-- Application Information --}}
                <div class="row mb-4">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Applicant
                        </label>

                        <input type="text" class="form-control" value="{{ $application->applicant->full_name }}"
                            disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Applied Job
                        </label>

                        <input type="text" class="form-control"
                            value="{{ $application->jobPosting->designation->name }}" disabled>
                    </div>

                </div>

                <form wire:submit="save">

                    <div class="row">

                        {{-- Application Status --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Application Status
                            </label>

                            <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror">

                                <option value="">
                                    Select Status
                                </option>

                                <option value="pending">
                                    Pending
                                </option>

                                <option value="shortlisted">
                                    Shortlisted
                                </option>

                                <option value="interview">
                                    Interview
                                </option>

                                <option value="rejected">
                                    Rejected
                                </option>

                                <option value="hired">
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

                        <hr>

                        <h5 class="fw-bold mb-3">
                            Interview Details
                        </h5>

                        <div class="row">

                            {{-- Interviewer --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
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


                            {{-- Scheduled At --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
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


                        <div class="row">

                            {{-- Interview Type --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Interview Mode
                                </label>

                                <select wire:model.live="mode"
                                    class="form-select
                    @error('mode') is-invalid @enderror">

                                    <option value="">
                                        Select Interview Type
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

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Interview Type
                                </label>

                                <select wire:model.live="type"
                                    class="form-select
                    @error('type') is-invalid @enderror">

                                    <option value="">
                                        Select Interview Type
                                    </option>

                                    <option value="technical">
                                        Technical
                                    </option>

                                    <option value="hr">
                                        Hr
                                    </option>



                                </select>

                                @error('type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>




                            {{-- Meeting Link --}}
                            @if ($mode === 'online')
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Meeting Link
                                    </label>

                                    <input type="url" wire:model="meeting_link"
                                        class="form-control
                        @error('meeting_link') is-invalid @enderror"
                                        placeholder="https://meet.google.com/...">

                                    @error('meeting_link')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                            @endif

                        </div>

                    @endif


                    <div class="d-flex justify-content-end mt-3">

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">

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
