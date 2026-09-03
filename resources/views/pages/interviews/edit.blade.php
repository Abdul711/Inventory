<?php

use Livewire\Component;
use App\Models\Interview;
use App\Models\InterviewFeedback;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public int $id;
    public $interview;

    // Interview fields
    public ?int $interviewer_id = null;
    public ?string $scheduled_at = null;
    public string $type = '';
    public string $mode = '';
    public string $status = '';
    public ?string $meeting_link = null;

    // Feedback fields
    public $communication_score = null;
    public $attitude_score = null;
    public $overall_score = null;
    public ?string $comments = null;
    public $recommended = null;

    public $interviewers = [];

    public function mount(int $id): void
    {
        $this->id = $id;

        // Here $id is interviews.id
        $this->interview = Interview::with(['applicant', 'jobApplication.jobPosting.designation'])->findOrFail($id);

        $this->interviewers = User::select('id', 'name', 'email')->orderBy('name')->get();

        $this->interviewer_id = $this->interview->interviewer_id;

        $this->scheduled_at = $this->interview->scheduled_at ? Carbon::parse($this->interview->scheduled_at)->format('Y-m-d\TH:i') : null;

        $this->type = $this->interview->type ?? '';
        $this->mode = $this->interview->mode ?? '';
        $this->status = $this->interview->status ?? 'scheduled';
        $this->meeting_link = $this->interview->meeting_link;

        $feedback = InterviewFeedback::where('interview_id', $this->interview->id)->first();

        if ($feedback) {
            $this->communication_score = $feedback->communication_score;
            $this->attitude_score = $feedback->attitude_score;
            $this->overall_score = $feedback->overall_score;
            $this->comments = $feedback->comments;

            $this->recommended = $feedback->recommended === null ? null : (string) (int) $feedback->recommended;
        }
    }
    public function updated($property)
    {
        if (str_contains($property, 'communication_score') || str_contains($property, 'attitude_score')) {
            $avg = ((float) $this->communication_score + (float) $this->attitude_score) / 2;

            $this->overall_score = round($avg, 2);
        }
    }
    public function updatedMode(string $value): void
    {
        if ($value !== 'online') {
            $this->meeting_link = null;
        }

        $this->resetValidation('meeting_link');
    }

    public function save(): void
    {
        $this->validate([
            'interviewer_id' => ['required', 'integer', 'exists:users,id'],
            'scheduled_at' => ['required', 'date'],
            'type' => ['required', 'in:technical,hr'],
            'mode' => ['required', 'in:online,physical,phone'],
            'status' => ['required', 'in:scheduled,completed,cancelled'],
            'meeting_link' => ['nullable', 'required_if:mode,online', 'url', 'max:255'],

            // Required when interview is completed
            'communication_score' => ['nullable', 'required_if:status,completed', 'integer', 'between:1,5'],
            'attitude_score' => ['nullable', 'required_if:status,completed', 'integer', 'between:1,5'],
            'overall_score' => ['nullable', 'required_if:status,completed', 'numeric', 'between:1,5'],
            'comments' => ['nullable', 'string', 'required_if:status,completed', 'max:2000'],
            'recommended' => ['nullable', 'required_if:status,completed', 'boolean'],
        ]);

        DB::transaction(function (): void {
            $this->interview->update([
                'interviewer_id' => $this->interviewer_id,
                'scheduled_at' => $this->scheduled_at,
                'type' => $this->type,
                'mode' => $this->mode,
                'status' => $this->status,
                'meeting_link' => $this->mode === 'online' ? $this->meeting_link : null,
            ]);

            $hasFeedback = collect([$this->communication_score, $this->attitude_score, $this->overall_score, $this->comments, $this->recommended])->contains(fn($value) => $value !== null && $value !== '');

            if ($this->status === 'completed' || $hasFeedback) {
                InterviewFeedback::updateOrCreate(
                    [
                        'interview_id' => $this->interview->id,
                    ],
                    [
                        'communication_score' => $this->communication_score,
                        'attitude_score' => $this->attitude_score,
                        'overall_score' => $this->overall_score,
                        'comments' => $this->comments,
                        'recommended' => $this->recommended,
                    ],
                );
            }
        });

        $this->interview->refresh();

        session()->flash('success', 'Interview and feedback updated successfully.');
    }
};

?>
<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Update Interview</h4>
            </div>

            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Read-only interview information --}}
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Applicant</label>

                        <input type="text" class="form-control"
                            value="{{ $interview->applicant?->full_name ?? 'N/A' }}" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Applied Job</label>

                        <input type="text" class="form-control"
                            value="{{ $interview->jobApplication?->jobPosting?->designation?->name ?? 'N/A' }}"
                            disabled>
                    </div>
                </div>

                <form wire:submit="save">
                    <h5 class="fw-bold mb-3">Interview Data</h5>

                    <div class="row">
                        {{-- Interviewer --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interviewer</label>

                            <select wire:model="interviewer_id"
                                class="form-select @error('interviewer_id')  is-invalid @enderror">
                                <option value="">Select Interviewer</option>

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

                        {{-- Scheduled date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Interview Date & Time
                            </label>

                            <input type="datetime-local" wire:model="scheduled_at"
                                class="form-control @error('scheduled_at') is-invalid @enderror">

                            @error('scheduled_at')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Interview type --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Type</label>

                            <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">Select Type</option>
                                <option value="technical">Technical</option>
                                <option value="hr">HR</option>
                            </select>

                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Interview mode --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Mode</label>

                            <select wire:model.live="mode" class="form-select @error('mode') is-invalid @enderror">
                                <option value="">Select Mode</option>
                                <option value="online">Online</option>
                                <option value="physical">Physical</option>
                                <option value="phone">Phone</option>
                            </select>

                            @error('mode')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Interview status --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Status</label>

                            <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="">Select Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>

                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @if ($mode == 'online')
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Meeting Link</label>

                                <input type="url" wire:model="meeting_link"
                                    class="form-control @error('meeting_link') is-invalid @enderror"
                                    placeholder="https://meet.google.com/..." readonly>

                                @error('meeting_link')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif
                    </div>




                    @if ($status == 'completed')
                        <hr>
                        <h5 class="fw-bold mb-3">Interview Feedback</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    Communication Score
                                </label>

                                <input type="number" min="1" max="5"
                                    wire:model.live="communication_score"
                                    class="form-control @error('communication_score') is-invalid @enderror">

                                @error('communication_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Attitude Score</label>

                                <input type="number" min="1" max="5" wire:model.live="attitude_score"
                                    class="form-control @error('attitude_score') is-invalid @enderror">

                                @error('attitude_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Overall Score</label>

                                <input type="number" min="1" max="5" step="0.1" readonly
                                    wire:model.live="overall_score"
                                    class="form-control @error('overall_score') is-invalid @enderror">

                                @error('overall_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label">Comments</label>

                                <textarea wire:model="comments" rows="4" class="form-control @error('comments') is-invalid @enderror"
                                    placeholder="Enter interview feedback..."></textarea>

                                @error('comments')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Recommended</label>

                                <select wire:model="recommended"
                                    class="form-select @error('recommended') is-invalid @enderror">
                                    <option value="">Select Recommendation</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>

                                @error('recommended')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                Update Interview
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
