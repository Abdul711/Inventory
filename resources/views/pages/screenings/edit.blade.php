<?php

use Livewire\Component;
use App\Models\Screening;
use App\Models\User;

new class extends Component {
    public $screening_id;
    public $job_application_id;

    public $screened_by = '';
    public $cv_score = '';
    public $experience_score = '';
    public $education_score = '';

    public $overall_score = 0;

    public $status = 'pending';

    public $strengths = '';
    public $weaknesses = '';
    public $remarks = '';

    public $screened_at = '';

    public $screeners = [];

    public function mount($id)
    {
        $screening = Screening::findOrFail($id);

        $this->screening_id = $screening->id;
        $this->job_application_id = $screening->job_application_id;

        $this->screened_by = $screening->screened_by;

        $this->cv_score = $screening->cv_score;
        $this->experience_score = $screening->experience_score;
        $this->education_score = $screening->education_score;

        $this->overall_score = $screening->overall_score ?? 0;

        $this->status = $screening->status;

        $this->strengths = $screening->strengths;
        $this->weaknesses = $screening->weaknesses;
        $this->remarks = $screening->remarks;

        $this->screened_at = $screening->screened_at ? \Carbon\Carbon::parse($screening->screened_at)->format('Y-m-d') : now()->format('Y-m-d');

        /*
        |--------------------------------------------------------------
        | Users allowed to perform screening
        |--------------------------------------------------------------
        |
        | Change role column according to your users table if needed.
        |
        */

        $this->screeners = User::query()
            ->whereIn('role_id', [1, 3])
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->calculateOverallScore();
    }

    public function rules()
    {
        return [
            'screened_by' => 'required|exists:users,id',

            'cv_score' => 'required|integer|min:1|max:5',
            'experience_score' => 'required|integer|min:1|max:5',
            'education_score' => 'required|integer|min:1|max:5',

            'status' => 'required|in:pending,completed,rejected,shortlisted',

            'strengths' => 'nullable|max:2000',
            'weaknesses' => 'nullable|max:2000',
            'remarks' => 'nullable|max:2000',

            'screened_at' => 'required|date',
        ];
    }

    protected $messages = [
        'screened_by.required' => 'Please select the person who screened the applicant.',

        'cv_score.required' => 'CV score is required.',
        'cv_score.min' => 'CV score must be at least 1.',
        'cv_score.max' => 'CV score cannot exceed 5.',

        'experience_score.required' => 'Experience score is required.',
        'experience_score.min' => 'Experience score must be at least 1.',
        'experience_score.max' => 'Experience score cannot exceed 5.',

        'education_score.required' => 'Education score is required.',
        'education_score.min' => 'Education score must be at least 1.',
        'education_score.max' => 'Education score cannot exceed 5.',

        'status.required' => 'Screening status is required.',
        'screened_at.required' => 'Screening date is required.',
    ];

    public function updated($property)
    {
        $this->validateOnly($property);

        if (in_array($property, ['cv_score', 'experience_score', 'education_score'])) {
            $this->calculateOverallScore();
        }
    }

    public function calculateOverallScore()
    {
        if ($this->cv_score && $this->experience_score && $this->education_score) {
            $this->overall_score = round(((int) $this->cv_score + (int) $this->experience_score + (int) $this->education_score) / 3, 2);
        } else {
            $this->overall_score = 0;
        }
    }

    public function update()
    {
        $this->validate();

        $this->calculateOverallScore();

        $screening = Screening::findOrFail($this->screening_id);

        $screening->update([
            'screened_by' => $this->screened_by,

            'cv_score' => $this->cv_score,
            'experience_score' => $this->experience_score,
            'education_score' => $this->education_score,

            'overall_score' => $this->overall_score,

            'status' => $this->status,

            'strengths' => $this->strengths ?: null,
            'weaknesses' => $this->weaknesses ?: null,
            'remarks' => $this->remarks ?: null,

            'screened_at' => $this->screened_at,
        ]);

        session()->flash('success', 'Screening updated successfully.');
    }
};

?>

<div class="row">

    <div class="col-lg-12">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Edit Screening
                </h4>

            </div>

            <div class="card-body">

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit="update">

                    {{-- Job Application --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Job Application
                        </label>

                        <input type="text" class="form-control" value="#{{ $job_application_id }}" disabled>

                    </div>


                    {{-- Screened By --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Screened By
                        </label>

                        <select wire:model.live="screened_by"
                            class="form-select @error('screened_by') is-invalid @enderror">

                            <option value="">
                                Select Screener
                            </option>

                            @foreach ($screeners as $screener)
                                <option value="{{ $screener['id'] }}">
                                    {{ $screener['name'] }}
                                </option>
                            @endforeach

                        </select>

                        @error('screened_by')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="row">

                        {{-- CV Score --}}

                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    CV Score
                                </label>

                                <select wire:model.live="cv_score"
                                    class="form-select @error('cv_score') is-invalid @enderror">

                                    <option value="">
                                        Select Score
                                    </option>

                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">
                                            {{ $i }}
                                        </option>
                                    @endfor

                                </select>

                                @error('cv_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Experience Score --}}

                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    Experience Score
                                </label>

                                <select wire:model.live="experience_score"
                                    class="form-select @error('experience_score') is-invalid @enderror">

                                    <option value="">
                                        Select Score
                                    </option>

                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">
                                            {{ $i }}
                                        </option>
                                    @endfor

                                </select>

                                @error('experience_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Education Score --}}

                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label">
                                    Education Score
                                </label>

                                <select wire:model.live="education_score"
                                    class="form-select @error('education_score') is-invalid @enderror">

                                    <option value="">
                                        Select Score
                                    </option>

                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">
                                            {{ $i }}
                                        </option>
                                    @endfor

                                </select>

                                @error('education_score')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Overall Score --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Overall Score
                        </label>

                        <input type="text" class="form-control fw-bold"
                            value="{{ number_format((float) $overall_score, 2) }} / 5" disabled>

                        <small class="text-muted">
                            Automatically calculated from CV, experience
                            and education scores.
                        </small>

                    </div>


                    {{-- Strengths --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Strengths
                        </label>

                        <textarea wire:model.live="strengths" rows="3" class="form-control @error('strengths') is-invalid @enderror"
                            placeholder="Enter applicant strengths"></textarea>

                        @error('strengths')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Weaknesses --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Weaknesses
                        </label>

                        <textarea wire:model.live="weaknesses" rows="3" class="form-control @error('weaknesses') is-invalid @enderror"
                            placeholder="Enter applicant weaknesses"></textarea>

                        @error('weaknesses')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Remarks --}}

                    <div class="mb-3">

                        <label class="form-label">
                            Remarks
                        </label>

                        <textarea wire:model.live="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                            placeholder="Enter screening remarks"></textarea>

                        @error('remarks')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="row">

                        {{-- Screening Date --}}

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">
                                    Screening Date
                                </label>

                                <input type="date" wire:model.live="screened_at"
                                    class="form-control @error('screened_at') is-invalid @enderror">

                                @error('screened_at')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Status --}}

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">
                                    Status
                                </label>

                                <select wire:model.live="status"
                                    class="form-select @error('status') is-invalid @enderror">

                                    <option value="pending">
                                        Pending
                                    </option>

                                    <option value="completed">
                                        Completed
                                    </option>

                                    <option value="shortlisted">
                                        Shortlisted
                                    </option>

                                    <option value="rejected">
                                        Rejected
                                    </option>

                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Submit --}}

                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="update"
                            @disabled(
                                $errors->has('cv_score') ||
                                    $errors->has('experience_score') ||
                                    $errors->has('education_score') ||
                                    empty($cv_score) ||
                                    empty($experience_score) ||
                                    empty($education_score))>

                            <span wire:loading.remove wire:target="update">
                                Update Screening
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
