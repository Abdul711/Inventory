<?php
use App\Models\ApplicantWork;
use App\Models\ApplicantEducation;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CandidateEducation;
use App\Models\CandidateDocument;
use App\Models\CandidateWork;
use App\Models\Applicant;
use Illuminate\Validation\Rule;
use App\Models\CandidatePortfolio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationSend;
use App\Jobs\GenerateCandidateDocuments;
new class extends Component {
    use WithFileUploads;

    /*
    |--------------------------------------------------------------------------
    | Job Information
    |--------------------------------------------------------------------------
    */
    function calculateTotalExperience(array $experiences): array
    {
        $periods = collect($experiences)
            ->filter(function ($experience) {
                return !empty($experience['from']) && !empty($experience['to']);
            })
            ->map(function ($experience) {
                return [
                    'from' => Carbon::parse($experience['from'])->startOfDay(),
                    'to' => Carbon::parse($experience['to'])->startOfDay(),
                ];
            })
            ->sortBy('from')
            ->values();

        if ($periods->isEmpty()) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => '0 Years',
            ];
        }

        // Merge overlapping periods
        $merged = [];

        foreach ($periods as $period) {
            if (empty($merged)) {
                $merged[] = $period;
                continue;
            }

            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            // Overlap
            if ($period['from']->lte($last['to'])) {
                // Extend the existing period
                if ($period['to']->gt($last['to'])) {
                    $merged[$lastIndex]['to'] = $period['to'];
                }
            } else {
                // Separate period
                $merged[] = $period;
            }
        }

        /*
         * Calculate total days.
         *
         * round() is important because Carbon can return
         * floating-point values in some date calculations.
         */
        $totalDays = 0;

        foreach ($merged as $period) {
            $totalDays += round($period['from']->diffInDays($period['to']));
        }

        $totalDays = (int) round($totalDays);

        /*
         * Convert total days to years/months/days.
         */
        $base = Carbon::create(2000, 1, 1);
        $end = $base->copy()->addDays($totalDays);
        $difference = $base->diff($end);
        return [
            'years' => $difference->y,
            'months' => $difference->m,
            'days' => $difference->d,
            'formatted' => "{$difference->y} Years " . "{$difference->m} Months " . "{$difference->d} Days",
        ];
    }
    public array $job = [
        'job_title' => 'Senior Laravel Developer',
        'department' => 'Engineering',
        'employment_type' => 'Full Time',
        'experience' => '3+ Years',
        'id' => '',
    ];
    public array $maritalStatuses = [
        'single' => 'Single',
        'married' => 'Married',
        'divorced' => 'Divorced',
    ];

    /*
    |--------------------------------------------------------------------------
    | Personal Information
    |--------------------------------------------------------------------------
    */

    public array $form = [
        'full_name' => '',
        'email' => '',
        'phone' => '',
        'cnic' => '',
        'dob' => '',
        'father_name' => '',
        'gender' => 'female',
        'password' => '',
        'martial_status' => 'single',
        'country' => '',
        'province' => '',
        'city' => '',
        'postal_code' => '',
        'address' => '',
        'current_company' => '',
        'current_designation' => '',
        'total_experience' => '',
        'relevant_experience' => '',
        'current_salary' => '',
        'expected_salary' => '',
        'notice_period' => '',
        'available_from' => '',
        'portfolio' => '',
        'github' => '',
        'linkedin' => '',
        'id' => '',
        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        */

        'why_join_us' => '',
        'about_yourself' => '',

        /*
        |--------------------------------------------------------------------------
        | Declaration
        |--------------------------------------------------------------------------
        */

        'declaration' => false,
    ];

    /*
    |--------------------------------------------------------------------------
    | Education
    |--------------------------------------------------------------------------
    */

    public array $educations = [];

    /*
    |--------------------------------------------------------------------------
    | Experience
    |--------------------------------------------------------------------------
    */

    public array $experiences = [];
    public array $documents = [];
    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    public $photo;

    public $coverLetter;

    public $certificates = [];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount($id): void
    {
        $id = (int) $id;

        if (auth('applicant')->check()) {
            $applicantworks = auth('applicant')->user()->works;
            $applicanteducations = auth('applicant')->user()->educations;

            foreach ($applicanteducations as $applicanteducation) {
                $this->educations[] = [
                    'degree' => $applicanteducation->degree_name,
                    'institute' => $applicanteducation->institute,

                    'grade' => $applicanteducation->grade,
                    'type' => $applicanteducation->institute_type,
                    'yearstart' => $applicanteducation->graduate_start_year,
                    'yearend' => $applicanteducation->graduate_end_year,
                ];
            }
            //company
            foreach ($applicantworks as $applicantwork) {
                $this->experiences[] = [
                    'company' => $applicantwork->company,
                    'designation' => $applicantwork->designation,
                    'from' => $applicantwork->start_date,
                    'to' => $applicantwork->end_date,
                    'responsibilities' => '',
                    'previous_salary' => $applicantwork->salary,
                ];
            }
            $this->form['id'] = auth('applicant')->user()->id;
            $this->form['full_name'] = auth('applicant')->user()->full_name;
            $this->form['email'] = auth('applicant')->user()->email;
            $this->form['father_name'] = auth('applicant')->user()->father_name;
            $this->form['phone'] = auth('applicant')->user()->phone;
            $this->form['cnic'] = auth('applicant')->user()->cnic;
            $this->form['linkedin'] = auth('applicant')->user()->linkedin;
            $this->form['dob'] = auth('applicant')->user()->date_of_birth;
            $this->form['martial_status'] = auth('applicant')->user()->martial_status;
            $this->form['gender'] = auth('applicant')->user()->gender;
            $this->form['address'] = auth('applicant')->user()->address;
            $this->photo = auth('applicant')->user()?->photo;
        } else {
            $this->educations[] = [
                'degree' => '',
                'institute' => '',
                'year' => '',
                'grade' => '',
                'type' => '',
                'yearstart' => '',
                'yearend' => '',
            ];

            $this->experiences[] = [
                'company' => '',
                'designation' => '',
                'from' => '',
                'to' => '',
                'previous_salary' => '',
                'responsibilities' => '',
            ];
        }

        $jobData = \App\Models\JobPosting::findOrFail($id);
        $this->job = [
            'job_title' => $jobData->designation->name,
            'employment_type' => str()->headline($jobData->employment_type),
            'department' => $jobData->department->name,
            'experience' => $jobData->min_experience,
            'id' => $id,
            'responsibilities' => $jobData->responsibilities,
            'requirements' => $jobData->requirements,
            'benefits' => $jobData->benefits,
            'work_mode' => ucfirst($jobData->work_mode),
            'avg_salary' => round(((int) $jobData->minimum_salary + (int) $jobData->maximum_salary) / 2),
        ];
    }
    public function updated($property)
    {
        if (str_contains($property, 'form.full_name')) {
            $this->form['linkedin'] = 'https://www.linkedin.com/in/' . str()->slug($this->form['full_name']);
        }
        if (str_contains($property, 'form.notice_period')) {
            $this->form['available_from'] = Carbon::today()->addMonths((int) $this->form['notice_period'])->format('Y-m-d');
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Education
    |--------------------------------------------------------------------------
    */
    public function addEducation(): void
    {
        $this->educations[] = [
            'degree' => '',
            'institute' => '',
            'year' => '',
            'grade' => '',
            'type' => '',
            'yearstart' => '',
            'yearend' => '',
        ];
    }

    public function removeEducation($index): void
    {
        unset($this->educations[$index]);

        $this->educations = array_values($this->educations);
    }

    /*
    |--------------------------------------------------------------------------
    | Experience
    |--------------------------------------------------------------------------
    */

    public function addExperience(): void
    {
        $this->experiences[] = [
            'company' => '',
            'designation' => '',
            'from' => '',
            'to' => '',
            'responsibilities' => '',
        ];
    }

    public function removeExperience($index): void
    {
        unset($this->experiences[$index]);

        $this->experiences = array_values($this->experiences);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        $isNewApplicant = empty($this->form['id']);

        return [
            'form.full_name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email'],
            'form.phone' => ['required'],
            'form.address' => ['required'],

            'photo' => [$isNewApplicant ? 'required' : 'nullable'],

            'form.cnic' => ['required', Rule::unique('applicants', 'cnic')->ignore($this->form['id'] ?? null)],
            'form.declaration' => ['accepted'],
            'educations.*.degree' => ['required'],
            'educations.*.type' => ['required'],
            'educations.*.yearstart' => ['required'],
            'form.expected_salary' => ['required'],
            'form.notice_period' => ['required'],
            'form.current_salary' => ['required'],
        ];
    }
    protected function messages(): array
    {
        return [
            'form.full_name.required' => 'Please enter your full name.',
            'form.email.required' => 'Please enter your email address.',
            'form.email.email' => 'Please enter a valid email address.',
            'form.phone.required' => 'Please enter your phone number.',
            'form.address.required' => 'Please enter your current address.',
            'photo.required' => 'Please upload your photo.',

            'form.declaration.accepted' => 'Please accept the declaration before submitting.', // Education
            'educations.*.degree.required' => 'Please enter your degree.',
            'educations.*.institute.required' => 'Please enter your institute name.',
            'educations.*.type.required' => 'Please select the institute type.',
            'educations.*.yearstart.required' => 'Please select the start date.',
            'educations.*.yearend.required' => 'Please select the end date.',
            'educations.*.yearend.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */
    public function removePhoto()
    {
        $this->photo = null;
    }
    public function goBack()
    {
        return redirect()->back();
    }
    public function rendering($view): void
    {
        $view->layout('components.layouts.ecommerce', [
            'cartCount' => 0,
        ]);
    }
    public function grade($percentage, $insurance_type)
    {
        if ($insurance_type == 'university') {
            if ($percentage >= 85 && $percentage <= 100) {
                $grade = 4.0;
            } elseif ($percentage >= 80) {
                $grade = 3.67;
            } elseif ($percentage >= 75) {
                $grade = 3.33;
            } elseif ($percentage >= 70) {
                $grade = 3.0;
            } elseif ($percentage >= 65) {
                $grade = 2.67;
            } elseif ($percentage >= 61) {
                $grade = 2.33;
            } elseif ($percentage >= 58) {
                $grade = 2.0;
            } elseif ($percentage >= 55) {
                $grade = 1.67;
            } elseif ($percentage >= 50) {
                $grade = 1.0;
            } else {
                $grade = 0.0;
            }
        } else {
            $grade = $percentage;
        }
        return $grade;
    }
    public function save()
    {
        set_time_limit(600);
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '512M');

        try {
            $this->validate();
            $totalExperience = $this->calculateTotalExperience($this->experiences);

            // $this->photo->getSize();
            $experience_in_year = $totalExperience['years'];

            if ($experience_in_year < $this->job['experience']) {
                session()->flash('error', 'You Are Not Qualified');
                return;
            }
            $jobId = $this->job['id'];
            $bio = 'I am' . $this->form['full_name'] . ' ';

            $applicant = Applicant::where('email', $this->form['email'])->first();

            if ($applicant) {
                $applicant_id = $applicant->id;
                $fileName = $applicant->photo;
            } else {
                $destinationDirectory = public_path('storage/applicant');
                if (!is_dir($destinationDirectory)) {
                    mkdir($destinationDirectory, 0755, true);
                }
                if ($this->photo) {
                    $fileName = 'applicant-' . time() . '-' . Str::random(8) . '.' . $this->photo->getClientOriginalExtension();
                    $photoPath = $this->photo->storeAs('applicant', $fileName, 'public');
                }

                $applicant = Applicant::create([
                    'full_name' => $this->form['full_name'],
                    'father_name' => $this->form['father_name'],
                    'email' => $this->form['email'],
                    'phone' => $this->form['phone'],
                    'password' => Hash::make('123456789'),
                    'date_of_birth' => $this->form['dob'],
                    'cnic' => $this->form['cnic'],
                    'address' => $this->form['address'],
                    'gender' => $this->form['gender'],
                    'bio' => $this->form['about_yourself'],
                    'martial_status' => $this->form['martial_status'],
                    'photo' => $fileName,

                    'linkedin' => $this->form['linkedin'],
                ]);
                $applicant_id = $applicant->id;
            }

            $count = JobApplication::where('applicant_id', $applicant_id)
                ->where('job_posting_id', $jobId)

                ->count();
            if ($count > 0) {
                session()->flash('error', 'You Have Already Applied');
                return;
            }
            $applicationData = DB::transaction(function () use ($jobId, $fileName, $totalExperience, $applicant) {
                $lasteducation = end($this->educations);
                $lastexperience = end($this->experiences);

                $application = JobApplication::create([
                    'applicant_id' => $applicant->id,
                    'job_posting_id' => $jobId,
                    'last_education' => $lasteducation['degree'],
                    'last_institute' => $lasteducation['institute'],
                    'current_company' => $lastexperience['company'],
                    'current_salary' => $this->form['current_salary'],
                    'expected_salary' => $this->form['expected_salary'],
                    'notice_period' => $this->form['notice_period'],
                    'available_from' => $this->form['available_from'],
                    'month_of_exprience' => $totalExperience['years'] * 12,
                ]);

                foreach ($this->educations as $candidateeducation) {
                    $degree = $candidateeducation['degree'];
                    $institute = $candidateeducation['institute'];
                    $grade = $candidateeducation['grade'];
                    $insurance_type = $candidateeducation['type'];
                    $yearstart = $candidateeducation['yearstart'];
                    $yearend = $candidateeducation['yearend'];

                    ApplicantEducation::firstOrCreate([
                        'applicant_id' => $applicant->id,
                        'degree_name' => $degree,
                        'grade' => $grade,
                        'institute' => $institute,
                        'institute_type' => $insurance_type,
                        'graduate_end_year' => $yearend,
                        'graduate_start_year' => $yearstart,
                    ]);

                    CandidateEducation::firstOrCreate([
                        'job_application_id' => $application->id,
                        'degree_name' => $degree,
                        'grade' => $this->grade($grade, $insurance_type),
                        'institute' => $institute,
                        'institute_type' => $insurance_type,
                        'graduate_end_year' => $yearend,
                        'graduate_start_year' => $yearstart,
                    ]);
                }

                foreach ($this->experiences as $candidate_exp) {
                    $from = Carbon::parse($candidate_exp['from']);
                    $to = Carbon::parse($candidate_exp['to']);
                    $difference = $from->diff($to);
                    $year = $difference->y;
                    $month_of_experience = $year * 12;
                    $experience = "{$difference->y} years, {$difference->m} months";

                    CandidateWork::firstOrCreate([
                        'job_application_id' => $application->id,
                        'company' => $candidate_exp['company'],
                        'start_date' => $from,
                        'end_date' => $to,
                        'designation' => $candidate_exp['designation'],
                        'month_of_experience' => $month_of_experience,
                    ]);
                    ApplicantWork::firstOrCreate([
                        'applicant_id' => $applicant->id,
                        'company' => $candidate_exp['company'],
                        'start_date' => $from,
                        'end_date' => $to,
                        'designation' => $candidate_exp['designation'],
                        'month_of_experience' => $month_of_experience,
                    ]);
                }

                return $application;
            });
            /*
             */
            GenerateCandidateDocuments::dispatch($applicationData->id);
            /*
            $this->reset(['form', 'photo', 'coverLetter', 'certificates']);
            */
            session()->flash(
                'success',

                'Your application has been submitted successfully.',
            );
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
};

?>
<div class="container py-5">
    {{-- ===========================
         SUCCESS MESSAGE
    ============================ --}}
    <div class="mb-4">
        <button type="button" wire:click="goBack"
            class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm hover-shadow">
            <i class="bi bi-arrow-left me-2"></i>
            Back to Job Listings
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success rounded-4 shadow-sm">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                </div>
                <div>
                    <h5 class="mb-1">Application Submitted Successfully</h5>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-success rounded-4 shadow-sm">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-x-circle-fill fs-2 text-danger"></i>
                </div>
                <div>

                    <p class="mb-0">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save">
        {{-- ===========================
             JOB SUMMARY
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-5">
                    <span class="badge bg-primary rounded-pill mb-3">APPLY FOR POSITION</span>
                    <h2 class="fw-bold">{{ $job['job_title'] }} ({{ $job['department'] }}) </h2>
                    <div class="row mt-4">

                        <div class="col-md-3">
                            <small class="text-muted">Employment Type</small>
                            <h6 class="fw-semibold">{{ $job['employment_type'] }}</h6>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Experience</small>
                            <h6 class="fw-semibold">{{ $job['experience'] }}</h6>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Work Mode</small>
                            <h6 class="fw-semibold">{{ $job['work_mode'] }}</h6>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Average Salary</small>
                            <h6 class="fw-semibold">{{ $job['avg_salary'] }}</h6>
                        </div>
                        {{-- Responsibilities --}}
                        <div class="card border-0 shadow mb-4">

                            <div class="card-header bg-light">
                                <h5 class="mb-0 fw-bold">
                                    Responsibilities
                                </h5>
                            </div>

                            <div class="card-body">

                                @if ($job['responsibilities'])
                                    <div>
                                        {!! nl2br(e($job['responsibilities'])) !!}
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        No responsibilities provided.
                                    </p>
                                @endif

                            </div>

                        </div>


                        {{-- Requirements --}}
                        <div class="card border-0 shadow mb-4">

                            <div class="card-header bg-light">
                                <h5 class="mb-0 fw-bold">
                                    Requirements
                                </h5>
                            </div>

                            <div class="card-body">

                                @if ($job['requirements'])
                                    <div>
                                        {!! nl2br(e($job['requirements'])) !!}
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        No requirements provided.
                                    </p>
                                @endif

                            </div>

                        </div>


                        {{-- Benefits --}}
                        <div class="card border-0 shadow mb-4">

                            <div class="card-header bg-light">
                                <h5 class="mb-0 fw-bold">
                                    Benefits
                                </h5>
                            </div>

                            <div class="card-body">

                                @if ($job['benefits'])
                                    <div>
                                        {!! nl2br(e($job['benefits'])) !!}
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        No benefits provided.
                                    </p>
                                @endif

                            </div>

                        </div>



                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             PERSONAL INFORMATION
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Personal Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input wire:model.live="form.full_name"
                                class="form-control @error('form.full_name') is-invalid @enderror"
                                placeholder="Enter your full name">
                            @error('form.full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Father Name <span class="text-danger">*</span></label>
                            <input wire:model.live="form.father_name"
                                class="form-control @error('form.father_name') is-invalid @enderror"
                                placeholder="Enter your father name">
                            @error('form.father_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" wire:model.live="form.email" autocomplete="off"
                                class="form-control @error('form.email') is-invalid @enderror"
                                placeholder="Enter your email address">
                            @error('form.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Phone Number <span
                                    class="text-danger">*</span></label>
                            <input wire:model.live="form.phone"
                                class="form-control @error('form.phone') is-invalid @enderror"
                                placeholder="Enter your phone number">
                            @error('form.phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" wire:model.live="form.password" autocomplete="off"
                                class="form-control @error('form.password') is-invalid @enderror"
                                placeholder="Enter your password">
                            @error('form.password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">CNIC</label>
                            <input wire:model.live="form.cnic" class="form-control"
                                placeholder="Enter your CNIC number">
                            @error('form.cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" wire:model.live="form.dob" class="form-control">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Gender</label>
                            <select wire:model.live="form.gender" class="form-select">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Martial Status</label>
                            <select wire:model.live="form.martial_status" class="form-select">
                                @foreach ($maritalStatuses as $key => $maritalStatus)
                                    <option value="{{ $key }}">{{ $maritalStatus }}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             ADDRESS
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Address Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">



                        <div class="col-12">
                            <label class="form-label fw-semibold">Current Address <span
                                    class="text-danger">*</span></label>
                            <textarea rows="4" wire:model.live="form.address"
                                class="form-control @error('form.address') is-invalid @enderror" placeholder="Enter your current address"></textarea>
                            @error('form.address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             PROFESSIONAL DETAILS
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Professional Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">



                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Current Salary (PKR)</label>
                            <input wire:model.live="form.current_salary" class="form-control"
                                placeholder="Enter current salary">
                        </div>
                        @error('form.current_salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Expected Salary (PKR)</label>
                            <input wire:model.live="form.expected_salary" class="form-control"
                                placeholder="Enter expected salary">
                        </div>
                        @error('form.expected_salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Notice Period</label>
                            <input wire:model.live="form.notice_period" class="form-control"
                                placeholder="e.g., 15 days, 1 month">
                        </div>
                        @error('form.notice_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Available From</label>
                            <input type="date" wire:model.live="form.available_from" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             EDUCATION
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">Education</h4>
                        <small class="text-muted">Add your educational background</small>
                    </div>
                    <button type="button" wire:click="addEducation" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-circle me-2"></i>Add Education
                    </button>
                </div>
                <div class="card-body">

                    @foreach ($educations as $index => $education)
                        <div wire:key="education-{{ $index }}" class="border rounded-4 p-4 bg-light mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-semibold mb-0">Education #{{ $loop->iteration }}</h5>
                                @if (count($educations) > 1 && $index > 0)
                                    <button type="button" wire:click="removeEducation({{ $index }})"
                                        class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                @endif
                            </div>
                            <div class="row">
                                @error('educations.' . $index . '.degree')
                                    <span class="invalid-feedback d-block">
                                        {{ $message }}
                                    </span>
                                @enderror
                                @error('educations.' . $index . '.type')
                                    <span class="invalid-feedback d-block">
                                        {{ $message }}
                                    </span>
                                @enderror
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Degree</label>
                                    <input type="text" wire:model.live="educations.{{ $index }}.degree"
                                        class="form-control" placeholder="e.g., BSCS">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Institute Type</label>
                                    <select wire:model.live="educations.{{ $index }}.type"
                                        class="form-control">
                                        <option>Select Institute Type</option>
                                        <option value="school">School</option>
                                        <option value="college">College</option>
                                        <option value="university">University</option>
                                    </select>
                                </div>


                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Institute</label>
                                    <input type="text" wire:model.live="educations.{{ $index }}.institute"
                                        class="form-control" placeholder="e.g., University Name">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Start</label>
                                    <input type="date" wire:model.live="educations.{{ $index }}.yearstart"
                                        class="form-control" placeholder="e.g., 2020-2024">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">End</label>
                                    <input type="date" wire:model.live="educations.{{ $index }}.yearend"
                                        class="form-control" placeholder="e.g., 2020-2024">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Grade</label>
                                    <input type="text" wire:model.live="educations.{{ $index }}.grade"
                                        class="form-control" placeholder="e.g., A+, 3.8 GPA">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===========================
             EXPERIENCE
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">Work Experience</h4>
                        <small class="text-muted">Add your previous employment history</small>
                    </div>
                    <button type="button" wire:click="addExperience" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-circle me-2"></i>Add Experience
                    </button>
                </div>
                <div class="card-body">
                    @foreach ($experiences as $index => $experience)
                        <div wire:key="experience-{{ $index }}" class="border rounded-4 p-4 bg-light mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-semibold mb-0">Experience #{{ $loop->iteration }}</h5>
                                @if (count($experiences) > 1 && $index > 0)
                                    <button type="button" wire:click="removeExperience({{ $index }})"
                                        class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Company</label>
                                    <input type="text" wire:model.live="experiences.{{ $index }}.company"
                                        class="form-control" placeholder="Enter company name">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">Designation</label>
                                    <input type="text"
                                        wire:model.live="experiences.{{ $index }}.designation"
                                        class="form-control" placeholder="Enter your designation">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">From</label>
                                    <input type="date" wire:model.live="experiences.{{ $index }}.from"
                                        class="form-control">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">To</label>
                                    <input type="date" wire:model.live="experiences.{{ $index }}.to"
                                        class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Responsibilities</label>
                                    <textarea rows="4" wire:model.live="experiences.{{ $index }}.responsibilities" class="form-control"
                                        placeholder="Describe your key responsibilities"></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===========================
             PORTFOLIO
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Portfolio</h4>
                </div>
                <div class="card-body">
                    <div class="row">


                        <div class="col-md-12 mb-4">
                            <label class="form-label fw-semibold">LinkedIn</label>
                            <input type="url" wire:model.live="form.linkedin" class="form-control"
                                placeholder="https://linkedin.com/in/username">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             DOCUMENTS
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Documents</h4>
                    <small class="text-muted">Upload your supporting documents</small>
                </div>
                <div class="card-body">
                    {{-- photo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Photo <span class="text-danger">*</span></label>
                        <input type="file" wire:model="photo"
                            class="form-control @error('photo') is-invalid @enderror" accept=".png,.jpeg,.jpg">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div wire:loading wire:target="photo" class="small text-primary mt-2">
                            <i class="bi bi-spinner bi-spin me-1"></i> Uploading photo...
                        </div>
                        @if ($photo)
                            <div class="mt-3">
                                @if (empty($form['id']))
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail"
                                        style="width: 200px; height: 200px; object-fit: cover;">
                                    <button type="button" wire:click="removePhoto"
                                        class="btn btn-danger btn-sm mt-2">
                                        Remove
                                    </button>
                                @else
                                    <img src="{{ asset('storage/applicant/' . $photo) }}" alt="Preview"
                                        class="img-thumbnail" style="width: 200px; height: 200px; object-fit: cover;">
                                    <button type="button" wire:click="removePhoto"
                                        class="btn btn-danger btn-sm mt-2">
                                        Remove
                                    </button>
                                @endif
                            </div>
                            @if (empty($form['id']))
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    {{ $photo->getClientOriginalName() }}
                                    ({{ number_format($photo->getSize() / 1024, 2) }} KB)
                                </div>
                            @endif
                        @endif
                    </div>




                    {{-- Upload Guidelines --}}
                    <div class="alert alert-info rounded-4 mt-4">
                        <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Upload Guidelines</h6>
                        <ul class="mb-0">
                            <li>photo must be PDF, DOC or DOCX format</li>
                            <li>Maximum file size: 5 MB per file</li>
                            <li>Certificates can be PDF or image files (JPG, JPEG, PNG)</li>
                            <li>Upload clear and readable documents</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             ADDITIONAL QUESTIONS
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h4 class="fw-bold mb-0">Additional Questions</h4>
                    <small class="text-muted">Help us know more about you</small>
                </div>
                <div class="card-body">
                    {{-- Why Join Us --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Why do you want to join our company?</label>
                        <textarea rows="5" wire:model.live="form.why_join_us" maxlength="1000"
                            class="form-control @error('form.why_join_us') is-invalid @enderror"
                            placeholder="Tell us why you would like to work with us..."></textarea>
                        @error('form.why_join_us')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-2">
                            <small class="text-muted">{{ strlen($form['why_join_us']) }}/1000</small>
                        </div>
                    </div>

                    {{-- About Yourself --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Tell us about yourself</label>
                        <textarea rows="6" wire:model.live="form.about_yourself" maxlength="2000"
                            class="form-control @error('form.about_yourself') is-invalid @enderror"
                            placeholder="Briefly introduce yourself, your strengths, achievements and career goals..."></textarea>
                        @error('form.about_yourself')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-2">
                            <small class="text-muted">{{ strlen($form['about_yourself']) }}/2000</small>
                        </div>
                    </div>

                    {{-- Availability --}}
                    <div class="row">

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Can You Work At Night Shift?</label>
                            <select wire:model.live="form.authorized" class="form-select">
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>

                    {{-- Optional Comments --}}

                </div>
            </div>
        </section>

        {{-- ===========================
             DECLARATION
        ============================ --}}
        <section class="mb-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-4">
                    <div class="form-check">
                        <input type="checkbox" id="declaration" wire:model.live="form.declaration"
                            class="form-check-input @error('form.declaration') is-invalid @enderror">
                        <label for="declaration" class="form-check-label">
                            I certify that all information provided in this application
                            is true, complete and accurate. I understand that providing
                            false information may result in rejection of my application
                            or termination of employment if hired.
                        </label>
                        @error('form.declaration')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- ===========================
             ACTION BUTTONS
        ============================ --}}
        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">
                    <i class="bi bi-send me-2"></i>Apply Now
                </span>
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Submitting Application...
                </span>
            </button>
        </div>
    </form>
</div>
