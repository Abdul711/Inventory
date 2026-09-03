<?php

namespace App\Livewire;

use App\Models\JobApplication as Application;
use App\Models\Interview;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $activeTab = 'dashboard';
    public $search = '';
    public $filterStatus = '';
    public $filterType = '';

    // Modal State Variables
    public $selectedApplicationId = null;
    public $selectedInterviewId = null;
    public $selectedOfferId = null;

    // Profile photo upload
    public $photo = null;
    public $photoPreview = null;

    // Salary Negotiation
    public $showNegotiateModal = false;
    public $negotiatingOfferId = null;
    public $proposedSalary = '';
    public $negotiationNotes = '';

    // NEW: Withdrawal and Decline
    public $showWithdrawModal = false;
    public $withdrawOfferId = null;
    public $withdrawReason = '';

    public $showDeclineModal = false;
    public $declineOfferId = null;
    public $declineReason = '';

    // Education Form Variables
    public $showEducationForm = false;
    public $editingEducationId = null;
    public $educationInstitution = '';
    public $educationDegree = '';
    public $educationField = '';
    public $educationStartDate = '';
    public $educationEndDate = '';
    public $educationGrade = '';
    public $educationDescription = '';
    public $educationCurrentlyStudying = false;

    // Work Experience Form Variables
    public $showExperienceForm = false;
    public $editingExperienceId = null;
    public $experienceCompany = '';
    public $experienceTitle = '';
    public $experienceLocation = '';
    public $experienceStartDate = '';
    public $experienceEndDate = '';
    public $experienceDescription = '';
    public $experienceCurrentlyWorking = false;
    public $experienceEmploymentType = 'full_time';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    protected array $maritalStatuses = [
        'single' => 'Single',
        'married' => 'Married',
        'divorced' => 'Divorced',
    ];

    // Statistics
    public function getStatsProperty()
    {
        return [
            'total_applications' => Application::where('applicant_id', auth('applicant')->id())->count(),
            'pending' => Application::where('applicant_id', auth('applicant')->id())
                ->where('status', 'pending')
                ->count(),
            'interview' => Application::where('applicant_id', auth('applicant')->id())
                ->where('status', 'interview')
                ->count(),
            'rejected' => Application::where('applicant_id', auth('applicant')->id())
                ->where('status', 'rejected')
                ->count(),
            'offered' => Application::where('applicant_id', auth('applicant')->id())
                ->where('status', 'offered')
                ->count(),
            'saved_jobs' => 10,
            'upcoming_interviews' => Interview::where('applicant_id', auth('applicant')->id())
                ->where('scheduled_at', '>', now())
                ->count(),
            'total_interviews' => Interview::where('applicant_id', auth('applicant')->id())->count(),
            'completed_interviews' => Interview::where('applicant_id', auth('applicant')->id())
                ->where('status', 'completed')
                ->count(),
        ];
    }

    // Recent Applications
    public function getRecentApplicationsProperty()
    {
        return [
            [
                'id' => 1,
                'job_title' => 'Senior Laravel Developer',
                'status' => 'interview',
                'applied_date' => '2024-01-15',
                'type' => 'Full Time',
                'has_interview' => true,
                'interview_date' => '2024-02-15',
                'interview_time' => '10:00 AM',
            ],
            [
                'id' => 2,
                'job_title' => 'Frontend Developer',
                'status' => 'interview',
                'applied_date' => '2024-01-12',
                'type' => 'Full Time',
                'has_interview' => true,
                'interview_date' => '2024-02-16',
                'interview_time' => '02:30 PM',
            ],
            [
                'id' => 3,
                'job_title' => 'UI/UX Designer',
                'status' => 'offered',
                'applied_date' => '2024-01-10',
                'type' => 'Contract',
                'has_interview' => false,
            ],
            [
                'id' => 4,
                'job_title' => 'DevOps Engineer',
                'company' => 'Cloud Systems',
                'status' => 'rejected',
                'applied_date' => '2024-01-08',
                'type' => 'Full Time',
                'has_interview' => false,
            ],
        ];
    }

    // Offers data (replace with real DB data)
    public function getOffersProperty()
    {
        return [
            [
                'id' => 3,
                'job_title' => 'UI/UX Designer',
                'company' => 'Creative Agency',
                'status' => 'offered',
                'applied_date' => '2024-01-10',
                'type' => 'Contract',
                'offer_details' => [
                    'salary' => '150,000 PKR/month',
                    'start_date' => '2024-03-01',
                    'position' => 'Senior UI/UX Designer',
                    'department' => 'Design',
                    'benefits' => 'Health insurance, 20 days annual leave, remote work allowance',
                    'offer_letter' => 'offer_letter_3.pdf',
                    'expiry_date' => '2024-02-28',
                    'contact_person' => 'Zara Malik',
                    'contact_email' => 'zara@creativeagency.com',
                    'additional_notes' => 'Please bring portfolio to the onboarding session.',
                ],
            ],
        ];
    }

    // Upcoming Interviews
    public function getUpcomingInterviewsProperty()
    {
        return [
            [
                'id' => 1,
                'job_title' => 'Senior Laravel Developer',
                'company' => 'Tech Solutions Inc.',
                'interviewer' => 'Sarah Ahmed',
                'date' => '2024-02-15',
                'time' => '10:00 AM',
                'type' => 'Technical',
                'mode' => 'Video Call',
                'status' => 'scheduled',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            ],
            [
                'id' => 2,
                'job_title' => 'Frontend Developer',
                'company' => 'Digital Innovations',
                'interviewer' => 'Ali Khan',
                'date' => '2024-02-16',
                'time' => '02:30 PM',
                'type' => 'HR',
                'mode' => 'In-person',
                'status' => 'scheduled',
                'meeting_link' => null,
            ],
            [
                'id' => 3,
                'job_title' => 'UI/UX Designer',
                'company' => 'Creative Agency',
                'interviewer' => 'Zara Malik',
                'date' => '2024-02-18',
                'time' => '11:00 AM',
                'type' => 'Portfolio Review',
                'mode' => 'Video Call',
                'status' => 'pending_confirmation',
                'meeting_link' => 'https://meet.google.com/xyz-uvwx-yza',
            ],
        ];
    }

    // Saved Jobs
    public function goBack() {}
    public function getSavedJobsProperty()
    {
        return [
            [
                'id' => 1,
                'title' => 'Full Stack Developer',
                'company' => 'Tech Startup',
                'location' => 'Karachi',
                'type' => 'Full Time',
                'salary' => '150,000 - 200,000',
                'posted_date' => '2024-01-18',
            ],
            [
                'id' => 2,
                'title' => 'Mobile App Developer',
                'company' => 'App Solutions',
                'location' => 'Lahore',
                'type' => 'Full Time',
                'salary' => '120,000 - 160,000',
                'posted_date' => '2024-01-16',
            ],
        ];
    }

    // Education Data
    public function getEducationsProperty()
    {
        return [
            [
                'id' => 1,
                'institution' => 'University of Karachi',
                'degree' => 'Bachelor of Science',
                'field' => 'Computer Science',
                'start_date' => '2018-09-01',
                'end_date' => '2022-06-30',
                'grade' => '3.8 GPA',
                'description' => 'Focused on software development and algorithms.',
                'currently_studying' => false,
            ],
            [
                'id' => 2,
                'institution' => 'Stanford University',
                'degree' => 'Master of Science',
                'field' => 'Artificial Intelligence',
                'start_date' => '2023-09-01',
                'end_date' => null,
                'grade' => null,
                'description' => 'Research in machine learning and neural networks.',
                'currently_studying' => true,
            ],
        ];
    }

    // Work Experience Data
    public function getExperiencesProperty()
    {
        return [
            [
                'id' => 1,
                'company' => 'Tech Solutions Inc.',
                'title' => 'Senior Laravel Developer',
                'location' => 'Karachi, Pakistan',
                'start_date' => '2022-07-01',
                'end_date' => null,
                'description' => 'Leading a team of 5 developers building enterprise applications using Laravel and Vue.js. Implemented CI/CD pipelines and improved code quality by 40%.',
                'currently_working' => true,
                'employment_type' => 'full_time',
            ],
            [
                'id' => 2,
                'company' => 'Digital Innovations',
                'title' => 'Full Stack Developer',
                'location' => 'Lahore, Pakistan',
                'start_date' => '2020-01-15',
                'end_date' => '2022-06-30',
                'description' => 'Developed and maintained multiple web applications using React, Node.js, and MySQL. Reduced loading time by 60% through optimization.',
                'currently_working' => false,
                'employment_type' => 'full_time',
            ],
            [
                'id' => 3,
                'company' => 'Freelance',
                'title' => 'Web Developer',
                'location' => 'Remote',
                'start_date' => '2019-06-01',
                'end_date' => '2019-12-31',
                'description' => 'Worked on various freelance projects including e-commerce websites and CMS development.',
                'currently_working' => false,
                'employment_type' => 'freelance',
            ],
        ];
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'profile') {
            $this->resetEducationForm();
            $this->resetExperienceForm();
        }
    }

    // -------- MODAL METHODS --------
    public function showApplication($id)
    {
        $this->selectedApplicationId = $id;
    }

    public function showInterview($id)
    {
        $this->selectedInterviewId = $id;
    }

    public function showOffer($id)
    {
        $this->selectedOfferId = $id;
    }

    public function closeModal()
    {
        $this->selectedApplicationId = null;
        $this->selectedInterviewId = null;
        $this->selectedOfferId = null;
    }

    public function emit()
    {
        auth('applicant')->logout();
        return redirect()->route('applicantauth');
    }

    // -------- SALARY NEGOTIATION --------
    public function openNegotiate($offerId)
    {
        $this->negotiatingOfferId = $offerId;
        $this->proposedSalary = '';
        $this->negotiationNotes = '';
        $this->showNegotiateModal = true;
    }

    public function closeNegotiate()
    {
        $this->showNegotiateModal = false;
        $this->negotiatingOfferId = null;
        $this->proposedSalary = '';
        $this->negotiationNotes = '';
    }

    public function submitNegotiation()
    {
        $this->validate([
            'proposedSalary' => 'required|string|max:255',
            'negotiationNotes' => 'nullable|string|max:1000',
        ]);

        session()->flash('success', 'Salary negotiation request sent successfully!');
        $this->closeNegotiate();
    }

    // -------- WITHDRAWAL (NEW) --------
    public function openWithdraw($offerId)
    {
        $this->withdrawOfferId = $offerId;
        $this->withdrawReason = '';
        $this->showWithdrawModal = true;
    }

    public function closeWithdraw()
    {
        $this->showWithdrawModal = false;
        $this->withdrawOfferId = null;
        $this->withdrawReason = '';
    }

    public function submitWithdraw()
    {
        $this->validate([
            'withdrawReason' => 'required|string|max:1000',
        ]);

        // Here you would update the application status to 'withdrawn' and store the reason.
        session()->flash('success', 'Your application has been withdrawn successfully.');
        $this->closeWithdraw();
    }

    // -------- DECLINE OFFER (NEW) --------
    public function openDecline($offerId)
    {
        $this->declineOfferId = $offerId;
        $this->declineReason = '';
        $this->showDeclineModal = true;
    }

    public function closeDecline()
    {
        $this->showDeclineModal = false;
        $this->declineOfferId = null;
        $this->declineReason = '';
    }

    public function submitDecline()
    {
        $this->validate([
            'declineReason' => 'required|string|max:1000',
        ]);

        // Here you would update the offer status to 'declined' and store the reason.
        session()->flash('success', 'Offer declined successfully.');
        $this->closeDecline();
    }

    // -------- PROFILE PHOTO UPLOAD --------
    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth('applicant')->user();

        if ($user->photo && Storage::disk('public')->exists('applicant/' . $user->photo)) {
            Storage::disk('public')->delete('applicant/' . $user->photo);
        }

        $path = $this->photo->store('applicant', 'public');
        $filename = basename($path);

        $user->photo = $filename;
        $user->save();

        $this->photo = null;
        $this->photoPreview = null;

        session()->flash('success', 'Profile photo updated successfully!');
    }

    // -------- EDUCATION MANAGEMENT --------
    public function resetEducationForm()
    {
        $this->educationInstitution = '';
        $this->educationDegree = '';
        $this->educationField = '';
        $this->educationStartDate = '';
        $this->educationEndDate = '';
        $this->educationGrade = '';
        $this->educationDescription = '';
        $this->educationCurrentlyStudying = false;
        $this->editingEducationId = null;
        $this->showEducationForm = false;
    }

    public function openEducationForm()
    {
        $this->resetEducationForm();
        $this->showEducationForm = true;
    }

    public function editEducation($id)
    {
        $educations = $this->educations;
        $education = collect($educations)->firstWhere('id', $id);

        if ($education) {
            $this->editingEducationId = $education['id'];
            $this->educationInstitution = $education['institution'];
            $this->educationDegree = $education['degree'];
            $this->educationField = $education['field'];
            $this->educationStartDate = $education['start_date'];
            $this->educationEndDate = $education['end_date'];
            $this->educationGrade = $education['grade'];
            $this->educationDescription = $education['description'];
            $this->educationCurrentlyStudying = $education['currently_studying'];
            $this->showEducationForm = true;
        }
    }

    public function saveEducation()
    {
        $this->validate([
            'educationInstitution' => 'required|string|max:255',
            'educationDegree' => 'required|string|max:255',
            'educationField' => 'required|string|max:255',
            'educationStartDate' => 'required|date',
            'educationEndDate' => 'nullable|date|after:educationStartDate',
            'educationGrade' => 'nullable|string|max:50',
            'educationDescription' => 'nullable|string',
            'educationCurrentlyStudying' => 'boolean',
        ]);

        if ($this->educationCurrentlyStudying) {
            $this->educationEndDate = null;
        }

        session()->flash('success', $this->editingEducationId ? 'Education updated successfully!' : 'Education added successfully!');
        $this->resetEducationForm();
        $this->showEducationForm = false;
    }

    public function deleteEducation($id)
    {
        session()->flash('success', 'Education entry removed successfully!');
    }

    public function cancelEducationForm()
    {
        $this->resetEducationForm();
        $this->showEducationForm = false;
    }

    // -------- WORK EXPERIENCE MANAGEMENT --------
    public function resetExperienceForm()
    {
        $this->experienceCompany = '';
        $this->experienceTitle = '';
        $this->experienceLocation = '';
        $this->experienceStartDate = '';
        $this->experienceEndDate = '';
        $this->experienceDescription = '';
        $this->experienceCurrentlyWorking = false;
        $this->experienceEmploymentType = 'full_time';
        $this->editingExperienceId = null;
        $this->showExperienceForm = false;
    }

    public function openExperienceForm()
    {
        $this->resetExperienceForm();
        $this->showExperienceForm = true;
    }

    public function editExperience($id)
    {
        $experiences = $this->experiences;
        $experience = collect($experiences)->firstWhere('id', $id);

        if ($experience) {
            $this->editingExperienceId = $experience['id'];
            $this->experienceCompany = $experience['company'];
            $this->experienceTitle = $experience['title'];
            $this->experienceLocation = $experience['location'];
            $this->experienceStartDate = $experience['start_date'];
            $this->experienceEndDate = $experience['end_date'];
            $this->experienceDescription = $experience['description'];
            $this->experienceCurrentlyWorking = $experience['currently_working'];
            $this->experienceEmploymentType = $experience['employment_type'];
            $this->showExperienceForm = true;
        }
    }

    public function saveExperience()
    {
        $this->validate([
            'experienceCompany' => 'required|string|max:255',
            'experienceTitle' => 'required|string|max:255',
            'experienceLocation' => 'required|string|max:255',
            'experienceStartDate' => 'required|date',
            'experienceEndDate' => 'nullable|date|after:experienceStartDate',
            'experienceDescription' => 'nullable|string',
            'experienceCurrentlyWorking' => 'boolean',
            'experienceEmploymentType' => 'required|in:full_time,part_time,contract,freelance,internship',
        ]);

        if ($this->experienceCurrentlyWorking) {
            $this->experienceEndDate = null;
        }

        session()->flash('success', $this->editingExperienceId ? 'Experience updated successfully!' : 'Experience added successfully!');
        $this->resetExperienceForm();
        $this->showExperienceForm = false;
    }

    public function deleteExperience($id)
    {
        session()->flash('success', 'Experience entry removed successfully!');
    }

    public function cancelExperienceForm()
    {
        $this->resetExperienceForm();
        $this->showExperienceForm = false;
    }

    public function applyJob($id)
    {
        return redirect()->route('applicant.apply', $id);
    }

    public function removeSavedJob($id)
    {
        session()->flash('success', 'Job removed from saved list.');
    }

    public function joinMeeting($link)
    {
        if ($link) {
            return redirect()->away($link);
        }
        session()->flash('error', 'Meeting link not available yet.');
    }

    public function rendering($view): void
    {
        $view->layout('components.layouts.ecommerce', [
            'cartCount' => 0,
        ]);
    }
};
?>

<div>
    {{-- HEADER --}}
    <div class="py-3 py-md-4 mb-4 shadow-sm">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3 gap-lg-2">
                <div class="text-center text-lg-start d-flex flex-column align-items-center align-items-lg-start">
                    <h4 class="mb-0 fw-bold fs-4 fs-md-3 fs-lg-2">Applicant Portal</h4>
                    <small class="text-white-50 fw-light mt-1 d-none d-sm-block">Manage your job applications &
                        interviews</small>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" wire:click="goBack"
                        class="btn btn-light btn-sm rounded-pill px-3 px-md-4 fw-medium shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </button>
                    <button type="button" wire:click="emit('logout')"
                        class="btn btn-light btn-sm rounded-pill px-3 px-md-4 fw-medium shadow-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success rounded-4 shadow-sm">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                    <div class="me-3 mb-2 mb-sm-0"><i class="bi bi-check-circle-fill fs-2 text-success"></i></div>
                    <div>
                        <h5 class="mb-1">Success!</h5>
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger rounded-4 shadow-sm">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                    <div class="me-3 mb-2 mb-sm-0"><i class="bi bi-exclamation-circle-fill fs-2 text-danger"></i></div>
                    <div>
                        <h5 class="mb-1">Error!</h5>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Navigation Tabs --}}
        <div class="mb-4">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-2 p-md-3">
                    <ul class="nav nav-pills flex-nowrap overflow-auto pb-1 pb-md-0"
                        style="-webkit-overflow-scrolling: touch;">
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('dashboard')"
                                class="nav-link {{ $activeTab === 'dashboard' ? 'active' : '' }} rounded-pill me-1">
                                <i class="bi bi-speedometer2 me-1"></i><span class="d-none d-sm-inline">Dashboard</span>
                            </button>
                        </li>
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('applications')"
                                class="nav-link {{ $activeTab === 'applications' ? 'active' : '' }} rounded-pill me-1">
                                <i class="bi bi-briefcase me-1"></i><span class="d-none d-sm-inline">Applications</span>
                            </button>
                        </li>
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('interviews')"
                                class="nav-link {{ $activeTab === 'interviews' ? 'active' : '' }} rounded-pill me-1">
                                <i class="bi bi-calendar-event me-1"></i><span
                                    class="d-none d-sm-inline">Interviews</span>
                                <span class="badge bg-danger ms-1">{{ $this->stats['upcoming_interviews'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('offers')"
                                class="nav-link {{ $activeTab === 'offers' ? 'active' : '' }} rounded-pill me-1">
                                <i class="bi bi-gift me-1"></i><span class="d-none d-sm-inline">Offers</span>
                                <span class="badge bg-success ms-1">{{ count($this->offers) }}</span>
                            </button>
                        </li>
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('saved')"
                                class="nav-link {{ $activeTab === 'saved' ? 'active' : '' }} rounded-pill me-1">
                                <i class="bi bi-bookmark me-1"></i><span class="d-none d-sm-inline">Saved Jobs</span>
                            </button>
                        </li>
                        <li class="nav-item flex-shrink-0">
                            <button wire:click="switchTab('profile')"
                                class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }} rounded-pill">
                                <i class="bi bi-person me-1"></i><span class="d-none d-sm-inline">Profile</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- DASHBOARD --}}
        @if ($activeTab === 'dashboard')
            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 small">Total Apps</h6>
                                    <h3 class="fw-bold mb-0 fs-4">{{ $this->stats['total_applications'] }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 p-md-3"><i
                                        class="bi bi-briefcase fs-5 text-primary"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 small">Upcoming</h6>
                                    <h3 class="fw-bold mb-0 fs-4">{{ $this->stats['upcoming_interviews'] }}</h3>
                                </div>
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 p-md-3"><i
                                        class="bi bi-clock-history fs-5 text-info"></i></div>
                            </div>
                            <div class="mt-2"><a href="#" wire:click.prevent="switchTab('interviews')"
                                    class="text-decoration-none small">View all <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 small">Total Int.</h6>
                                    <h3 class="fw-bold mb-0 fs-4">{{ $this->stats['total_interviews'] }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 p-md-3"><i
                                        class="bi bi-calendar-check fs-5 text-success"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1 small">Saved</h6>
                                    <h3 class="fw-bold mb-0 fs-4">{{ $this->stats['saved_jobs'] }}</h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 p-md-3"><i
                                        class="bi bi-bookmark fs-5 text-warning"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Interviews Quick View --}}
            <div class="card border-0 shadow rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i
                                class="bi bi-calendar-event text-primary me-2"></i>Upcoming Interviews</h5>
                        <button wire:click="switchTab('interviews')"
                            class="btn btn-link text-decoration-none small">View All <i
                                class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Job Title</th>
                                    <th>Date & Time</th>
                                    <th class="d-none d-lg-table-cell">Type</th>
                                    <th>Status</th>
                                    <th class="pe-3 pe-md-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->upcomingInterviews as $interview)
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-semibold small">{{ $interview['job_title'] }}</td>
                                        <td>
                                            <div class="small">
                                                {{ \Carbon\Carbon::parse($interview['date'])->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $interview['time'] }}</small>
                                        </td>
                                        <td class="d-none d-lg-table-cell"><span
                                                class="badge bg-secondary small">{{ $interview['type'] }}</span></td>
                                        <td><span
                                                class="badge bg-{{ $interview['status'] === 'scheduled' ? 'success' : 'warning' }} rounded-pill small">{{ $interview['status'] === 'scheduled' ? 'Confirmed' : 'Pending' }}</span>
                                        </td>
                                        <td class="pe-3 pe-md-4 text-end">
                                            <div class="d-flex justify-content-end gap-1 gap-md-2">
                                                @if ($interview['meeting_link'])
                                                    <button
                                                        wire:click="joinMeeting('{{ $interview['meeting_link'] }}')"
                                                        class="btn btn-sm btn-primary rounded-pill px-2 px-md-3"><i
                                                            class="bi bi-camera-video"></i></button>
                                                @endif
                                                <button wire:click="showInterview({{ $interview['id'] }})"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-2 px-md-3">Details</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted"><i
                                                class="bi bi-calendar-check fs-2 d-block mb-2"></i>No upcoming
                                            interviews</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Applications --}}
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 fs-6 fs-md-5">Recent Applications</h5>
                        <button wire:click="switchTab('applications')"
                            class="btn btn-link text-decoration-none small">View All <i
                                class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Job Title</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th class="pe-3 pe-md-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->recentApplications as $application)
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-semibold small">{{ $application['job_title'] }}
                                        </td>
                                        <td class="small">
                                            {{ \Carbon\Carbon::parse($application['applied_date'])->diffForHumans() }}
                                        </td>
                                        <td>
                                            @php $statusColors = ['pending'=>'warning','interview'=>'info','offered'=>'success','rejected'=>'danger']; @endphp
                                            <span
                                                class="badge bg-{{ $statusColors[$application['status']] ?? 'secondary' }} rounded-pill small">{{ ucfirst($application['status']) }}</span>
                                        </td>
                                        <td class="pe-3 pe-md-4 text-end">
                                            <button wire:click="showApplication({{ $application['id'] }})"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-2 px-md-3 small">View</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted"><i
                                                class="bi bi-inbox fs-2 d-block mb-2"></i>No applications found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- APPLICATIONS --}}
        @if ($activeTab === 'applications')
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-white border-0 p-3 p-md-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5">All Applications</h5>
                        </div>
                        <div class="col-8 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-search"></i></span>
                                <input wire:model.live="search" type="text" class="form-control border-start-0"
                                    placeholder="Search...">
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <select wire:model.live="filterStatus" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="interview">Interview</option>
                                <option value="offered">Offered</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Job Title</th>
                                    <th class="d-none d-lg-table-cell">Type</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th class="pe-3 pe-md-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->recentApplications as $application)
                                    <tr>
                                        <td class="ps-3 ps-md-4 fw-semibold small">{{ $application['job_title'] }}
                                        </td>
                                        <td class="d-none d-lg-table-cell"><span
                                                class="badge bg-secondary small">{{ $application['type'] }}</span>
                                        </td>
                                        <td class="small">{{ $application['applied_date'] }}</td>
                                        <td>
                                            @php $statusColors = ['pending'=>'warning','interview'=>'info','offered'=>'success','rejected'=>'danger']; @endphp
                                            <span
                                                class="badge bg-{{ $statusColors[$application['status']] ?? 'secondary' }} rounded-pill small">{{ ucfirst($application['status']) }}</span>
                                        </td>
                                        <td class="pe-3 pe-md-4 text-end">
                                            <button wire:click="showApplication({{ $application['id'] }})"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-2 px-md-3 small">View</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted"><i
                                                class="bi bi-inbox fs-2 d-block mb-2"></i>No applications found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- INTERVIEWS --}}
        @if ($activeTab === 'interviews')
            <div class="row g-3">
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow rounded-4">
                                <div class="card-body p-2 p-md-3 text-center">
                                    <h6 class="text-muted mb-1 small">Upcoming</h6>
                                    <h4 class="fw-bold text-info mb-0 fs-5">{{ $this->stats['upcoming_interviews'] }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow rounded-4">
                                <div class="card-body p-2 p-md-3 text-center">
                                    <h6 class="text-muted mb-1 small">Completed</h6>
                                    <h4 class="fw-bold text-success mb-0 fs-5">
                                        {{ $this->stats['completed_interviews'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow rounded-4">
                                <div class="card-body p-2 p-md-3 text-center">
                                    <h6 class="text-muted mb-1 small">Total</h6>
                                    <h4 class="fw-bold text-primary mb-0 fs-5">{{ $this->stats['total_interviews'] }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow rounded-4">
                                <div class="card-body p-2 p-md-3 text-center">
                                    <h6 class="text-muted mb-1 small">Pending</h6>
                                    <h4 class="fw-bold text-warning mb-0 fs-5">1</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow rounded-4">
                        <div class="card-header bg-white border-0 p-3 p-md-4">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i
                                    class="bi bi-clock-history text-info me-2"></i>Upcoming Interviews</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 ps-md-4">Job Title</th>
                                            <th class="d-none d-lg-table-cell">Interviewer</th>
                                            <th>Date & Time</th>
                                            <th class="d-none d-xl-table-cell">Mode</th>
                                            <th>Status</th>
                                            <th class="pe-3 pe-md-4 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($this->upcomingInterviews as $interview)
                                            <tr>
                                                <td class="ps-3 ps-md-4 fw-semibold small">
                                                    {{ $interview['job_title'] }}</td>
                                                <td class="d-none d-lg-table-cell small">
                                                    {{ $interview['interviewer'] }}</td>
                                                <td>
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($interview['date'])->format('M d, Y') }}
                                                    </div><small class="text-muted">{{ $interview['time'] }}</small>
                                                </td>
                                                <td class="d-none d-xl-table-cell"><span
                                                        class="badge bg-light text-dark small"><i
                                                            class="bi bi-{{ $interview['mode'] === 'Video Call' ? 'camera-video' : ($interview['mode'] === 'In-person' ? 'building' : 'telephone') }} me-1"></i>{{ $interview['mode'] }}</span>
                                                </td>
                                                <td><span
                                                        class="badge bg-{{ $interview['status'] === 'scheduled' ? 'success' : 'warning' }} rounded-pill small">{{ $interview['status'] === 'scheduled' ? 'Confirmed' : 'Pending' }}</span>
                                                </td>
                                                <td class="pe-3 pe-md-4 text-end">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        @if ($interview['meeting_link'])
                                                            <button
                                                                wire:click="joinMeeting('{{ $interview['meeting_link'] }}')"
                                                                class="btn btn-sm btn-primary rounded-pill px-2 px-md-3"><i
                                                                    class="bi bi-camera-video"></i></button>
                                                        @endif
                                                        <button wire:click="showInterview({{ $interview['id'] }})"
                                                            class="btn btn-sm btn-outline-primary rounded-pill px-2 px-md-3 small">Details</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted"><i
                                                        class="bi bi-calendar-check fs-2 d-block mb-2"></i>No upcoming
                                                    interviews scheduled</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow rounded-4">
                        <div class="card-header bg-white border-0 p-3 p-md-4">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i
                                    class="bi bi-lightbulb text-warning me-2"></i>Interview Preparation Tips</h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-2 bg-light rounded-4 h-100">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-inline-block mb-1"><i
                                                class="bi bi-search fs-5 text-primary"></i></div>
                                        <h6 class="fw-bold mb-0 small">Research</h6><small
                                            class="text-muted d-none d-sm-block">Company info</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-2 bg-light rounded-4 h-100">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2 d-inline-block mb-1"><i
                                                class="bi bi-clipboard-check fs-5 text-success"></i></div>
                                        <h6 class="fw-bold mb-0 small">Prepare</h6><small
                                            class="text-muted d-none d-sm-block">Common Qs</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-2 bg-light rounded-4 h-100">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-2 d-inline-block mb-1"><i
                                                class="bi bi-laptop fs-5 text-info"></i></div>
                                        <h6 class="fw-bold mb-0 small">Tech Setup</h6><small
                                            class="text-muted d-none d-sm-block">Test equipment</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-2 bg-light rounded-4 h-100">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-inline-block mb-1"><i
                                                class="bi bi-question-circle fs-5 text-warning"></i></div>
                                        <h6 class="fw-bold mb-0 small">Ask Qs</h6><small
                                            class="text-muted d-none d-sm-block">Your questions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- OFFERS TAB (with Withdraw & Decline buttons) --}}
        @if ($activeTab === 'offers')
            <div class="row g-3">
                @forelse ($this->offers as $offer)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow rounded-4 h-100">
                            <div class="card-body p-3 p-md-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0 fs-6">{{ $offer['job_title'] }}</h5>
                                    <span class="badge bg-success rounded-pill small">Offered</span>
                                </div>
                                <p class="mb-2 small"><i class="bi bi-building me-1"></i>{{ $offer['company'] }}</p>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark small">{{ $offer['type'] }}</span>
                                    <span class="badge bg-info text-dark small ms-1"><i
                                            class="bi bi-currency-rupee me-1"></i>{{ $offer['offer_details']['salary'] }}</span>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Start Date:
                                            {{ \Carbon\Carbon::parse($offer['offer_details']['start_date'])->format('M d, Y') }}</small>
                                        <small class="text-muted">Expires:
                                            {{ \Carbon\Carbon::parse($offer['offer_details']['expiry_date'])->format('M d, Y') }}</small>
                                    </div>
                                    {{-- Action buttons row --}}
                                    <div class="d-flex flex-wrap gap-2">
                                        <button wire:click="showOffer({{ $offer['id'] }})"
                                            class="btn btn-outline-primary rounded-pill flex-fill small"><i
                                                class="bi bi-eye me-1"></i> View</button>
                                        <button wire:click="openNegotiate({{ $offer['id'] }})"
                                            class="btn btn-outline-warning rounded-pill flex-fill small"><i
                                                class="bi bi-currency-dollar me-1"></i> Negotiate</button>
                                        {{-- NEW: Withdraw button --}}
                                        <button wire:click="openWithdraw({{ $offer['id'] }})"
                                            class="btn btn-outline-danger rounded-pill flex-fill small"><i
                                                class="bi bi-x-circle me-1"></i> Withdraw</button>
                                        {{-- NEW: Decline button --}}
                                        <button wire:click="openDecline({{ $offer['id'] }})"
                                            class="btn btn-outline-dark rounded-pill flex-fill small"><i
                                                class="bi bi-hand-thumbs-down me-1"></i> Decline</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow rounded-4">
                            <div class="card-body p-4 text-center">
                                <i class="bi bi-gift fs-1 text-muted d-block mb-3"></i>
                                <h5 class="fw-bold fs-6">No Job Offers Yet</h5>
                                <p class="text-muted small">You haven't received any job offers at the moment.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- SAVED JOBS --}}
        @if ($activeTab === 'saved')
            <div class="row g-3">
                @forelse ($this->savedJobs as $job)
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow rounded-4 h-100">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1 fs-6">{{ $job['title'] }}</h5>
                                    </div>
                                    <button wire:click="removeSavedJob({{ $job['id'] }})"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-2"><i
                                            class="bi bi-bookmark-fill"></i></button>
                                </div>
                                <div class="mb-3 d-flex flex-wrap gap-1">
                                    <span class="badge bg-secondary small">{{ $job['type'] }}</span>
                                    <span class="badge bg-light text-dark small">{{ $job['location'] }}</span>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div><small class="text-muted d-block small">Salary</small>
                                        <p class="fw-semibold mb-0 small">PKR {{ $job['salary'] }}</p>
                                    </div>
                                    <div class="text-end"><small class="text-muted d-block small">Posted</small>
                                        <p class="mb-0 small">
                                            {{ \Carbon\Carbon::parse($job['posted_date'])->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="mt-3"><button wire:click="applyJob({{ $job['id'] }})"
                                        class="btn btn-primary w-100 rounded-pill small"><i
                                            class="bi bi-send me-2"></i>Apply Now</button></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow rounded-4">
                            <div class="card-body p-4 text-center">
                                <i class="bi bi-bookmark fs-1 text-muted d-block mb-3"></i>
                                <h5 class="fw-bold fs-6">No Saved Jobs</h5>
                                <p class="text-muted small">Start saving jobs you're interested in.</p>
                                <a href="{{ route('applicant.jobs') }}"
                                    class="btn btn-primary rounded-pill small">Browse Jobs</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- PROFILE --}}
        @if ($activeTab === 'profile')
            <div class="row g-3">
                {{-- Profile Overview --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow rounded-4">
                        <div class="card-body p-3 p-md-4 text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <img src="{{ asset('storage/applicant/' . auth('applicant')->user()->photo) }}"
                                    alt="Profile" class="rounded-circle"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                                <label for="profilePhotoInput"
                                    class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-1"
                                    style="cursor: pointer;">
                                    <i class="bi bi-camera small"></i>
                                </label>
                                <input type="file" id="profilePhotoInput" wire:model="photo" class="d-none"
                                    accept="image/*">
                                <div wire:loading wire:target="photo"
                                    class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-2">
                                    <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                                </div>
                                @if ($photoPreview)
                                    <img src="{{ $photoPreview->temporaryUrl() }}"
                                        class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
                                        style="object-fit: cover; opacity: 0.7;">
                                @endif
                            </div>
                            <h5 class="fw-bold fs-6">{{ auth('applicant')->user()->full_name }}</h5>
                            <p class="text-muted small">
                                {{ auth('applicant')->user()->works?->last()?->designation ?? 'N/A' }}</p>
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <span
                                    class="badge bg-primary rounded-pill small">{{ round(auth('applicant')->user()->works->sum('month_of_experience') / 12) }}
                                    Years</span>
                                <span class="badge bg-success rounded-pill small">Available</span>
                            </div>
                            <hr>
                            <div class="row g-1 text-start">
                                <div class="col-6"><small
                                        class="text-muted d-block small">Applications</small><strong
                                        class="small">{{ $this->stats['total_applications'] }}</strong></div>
                                <div class="col-6"><small class="text-muted d-block small">Interviews</small><strong
                                        class="small">{{ $this->stats['interview'] }}</strong></div>
                                <div class="col-6"><small class="text-muted d-block small">Offers</small><strong
                                        class="small">{{ $this->stats['offered'] }}</strong></div>
                                <div class="col-6"><small class="text-muted d-block small">Saved</small><strong
                                        class="small">{{ $this->stats['saved_jobs'] }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Profile Details & Education & Experience --}}
                <div class="col-md-8">
                    {{-- Profile Details Form --}}
                    <div class="card border-0 shadow rounded-4 mb-3">
                        <div class="card-header bg-white border-0 p-3 p-md-4">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5">Profile Details</h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form wire:submit.prevent="updateProfile">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold small">Full
                                            Name</label><input type="text" class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->full_name }}"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-semibold small">Email</label><input type="email"
                                            class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->email }}"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-semibold small">Phone</label><input type="text"
                                            class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->phone }}"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold small">Father
                                            Name</label><input type="text" class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->father_name }}"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-semibold small">CNIC</label><input type="text"
                                            class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->cnic }}"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold small">Date Of
                                            Birth</label><input type="date" class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->date_of_birth }}"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-semibold small">LinkedIn</label><input type="text"
                                            class="form-control form-control-sm"
                                            value="{{ auth('applicant')->user()->linkedin }}"></div>
                                    <div class="col-md-6"><label
                                            class="form-label fw-semibold small">Password</label><input type="text"
                                            class="form-control form-control-sm" value=""></div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">Marital Status</label>
                                            <div class="form-check form-check-inline"><input class="form-check-input"
                                                    type="radio" name="marital_status" id="married"
                                                    value="married"><label class="form-check-label"
                                                    for="married">Married</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input"
                                                    type="radio" name="marital_status" id="unmarried"
                                                    value="Unmarried"><label class="form-check-label"
                                                    for="unmarried">Unmarried</label></div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">Gender</label>
                                            <div class="form-check form-check-inline"><input class="form-check-input"
                                                    type="radio" name="gender" id="male"
                                                    value="Male"><label class="form-check-label"
                                                    for="male">Male</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input"
                                                    type="radio" name="gender" id="female"
                                                    value="Female"><label class="form-check-label"
                                                    for="female">Female</label></div>
                                        </div>
                                    </div>
                                    <div class="col-12"><label class="form-label fw-semibold small">About</label>
                                        <textarea rows="2" class="form-control form-control-sm">Experienced Laravel developer with 5+ years of experience...</textarea>
                                    </div>
                                    <div class="col-12"><button type="submit"
                                            class="btn btn-primary rounded-pill px-4 px-md-5 small w-100 w-md-auto"><i
                                                class="bi bi-save me-2"></i>Update Profile</button></div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Work Experience --}}
                    <div class="card border-0 shadow rounded-4 mb-3">
                        <div class="card-header bg-white border-0 p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i
                                        class="bi bi-briefcase text-primary me-2"></i>Work Experience</h5>
                                @if (!$showExperienceForm)
                                    <button wire:click="openExperienceForm"
                                        class="btn btn-primary btn-sm rounded-pill px-3"><i
                                            class="bi bi-plus-circle me-1"></i> Add Experience</button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @if ($showExperienceForm)
                                <div class="bg-light p-3 p-md-4 rounded-4 mb-4">
                                    <h6 class="fw-bold mb-3">
                                        {{ $editingExperienceId ? 'Edit Experience' : 'Add New Experience' }}</h6>
                                    <form wire:submit.prevent="saveExperience">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Company
                                                    *</label><input type="text" wire:model="experienceCompany"
                                                    class="form-control form-control-sm @error('experienceCompany') is-invalid @enderror"
                                                    placeholder="Company name">
                                                @error('experienceCompany')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Job
                                                    Title *</label><input type="text" wire:model="experienceTitle"
                                                    class="form-control form-control-sm @error('experienceTitle') is-invalid @enderror"
                                                    placeholder="e.g. Senior Developer">
                                                @error('experienceTitle')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Location
                                                    *</label><input type="text" wire:model="experienceLocation"
                                                    class="form-control form-control-sm @error('experienceLocation') is-invalid @enderror"
                                                    placeholder="City, Country">
                                                @error('experienceLocation')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label
                                                    class="form-label fw-semibold small">Employment Type
                                                    *</label><select wire:model="experienceEmploymentType"
                                                    class="form-select form-select-sm @error('experienceEmploymentType') is-invalid @enderror">
                                                    <option value="full_time">Full Time</option>
                                                    <option value="part_time">Part Time</option>
                                                    <option value="contract">Contract</option>
                                                    <option value="freelance">Freelance</option>
                                                    <option value="internship">Internship</option>
                                                </select>
                                                @error('experienceEmploymentType')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Start
                                                    Date *</label><input type="date"
                                                    wire:model="experienceStartDate"
                                                    class="form-control form-control-sm @error('experienceStartDate') is-invalid @enderror">
                                                @error('experienceStartDate')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">End
                                                    Date</label><input type="date" wire:model="experienceEndDate"
                                                    class="form-control form-control-sm @error('experienceEndDate') is-invalid @enderror"
                                                    {{ $experienceCurrentlyWorking ? 'disabled' : '' }}>
                                                @error('experienceEndDate')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check"><input type="checkbox"
                                                        wire:model="experienceCurrentlyWorking"
                                                        class="form-check-input" id="currentlyWorking"><label
                                                        class="form-check-label small" for="currentlyWorking">I am
                                                        currently working here</label></div>
                                            </div>
                                            <div class="col-12"><label
                                                    class="form-label fw-semibold small">Description</label>
                                                <textarea wire:model="experienceDescription" class="form-control form-control-sm" rows="3"
                                                    placeholder="Describe your responsibilities and achievements..."></textarea>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap gap-2"><button type="submit"
                                                        class="btn btn-primary rounded-pill px-4 small"><i
                                                            class="bi bi-check-circle me-1"></i>{{ $editingExperienceId ? 'Update' : 'Save' }}</button><button
                                                        type="button" wire:click="cancelExperienceForm"
                                                        class="btn btn-secondary rounded-pill px-4 small">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                            @if (count($this->experiences) > 0)
                                <div class="row g-3">
                                    @foreach ($this->experiences as $experience)
                                        <div class="col-12">
                                            <div
                                                class="d-flex justify-content-between align-items-start p-3 bg-light rounded-4">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                        <h6 class="fw-bold mb-0">{{ $experience['title'] }}</h6>
                                                        @if ($experience['currently_working'])
                                                            <span
                                                                class="badge bg-success rounded-pill small">Current</span>
                                                        @endif
                                                        @php $employmentTypeLabels = ['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','freelance'=>'Freelance','internship'=>'Internship']; @endphp
                                                        <span
                                                            class="badge bg-secondary rounded-pill small">{{ $employmentTypeLabels[$experience['employment_type']] ?? $experience['employment_type'] }}</span>
                                                    </div>
                                                    <p class="mb-1 small"><i
                                                            class="bi bi-building me-1"></i>{{ $experience['company'] }}
                                                        <span class="text-muted">·
                                                            {{ $experience['location'] }}</span>
                                                    </p>
                                                    <div class="d-flex flex-wrap gap-2 mb-1"><small
                                                            class="text-muted"><i
                                                                class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($experience['start_date'])->format('M Y') }}
                                                            @if ($experience['end_date'])
                                                                -
                                                                {{ \Carbon\Carbon::parse($experience['end_date'])->format('M Y') }}
                                                            @else
                                                                - Present
                                                            @endif
                                                        </small>
                                                    </div>
                                                    @if ($experience['description'])
                                                        <p class="mb-0 small text-muted">
                                                            {{ $experience['description'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-1 flex-shrink-0 ms-2">
                                                    <button wire:click="editExperience({{ $experience['id'] }})"
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-2"><i
                                                            class="bi bi-pencil"></i></button>
                                                    <button wire:click="deleteExperience({{ $experience['id'] }})"
                                                        wire:confirm="Are you sure you want to delete this experience entry?"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-2"><i
                                                            class="bi bi-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4"><i
                                        class="bi bi-briefcase fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0 small">No work experience added yet.</p>
                                    <p class="text-muted small">Click "Add Experience" to get started.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Education --}}
                    <div class="card border-0 shadow rounded-4">
                        <div class="card-header bg-white border-0 p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i
                                        class="bi bi-mortarboard text-primary me-2"></i>Education</h5>
                                @if (!$showEducationForm)
                                    <button wire:click="openEducationForm"
                                        class="btn btn-primary btn-sm rounded-pill px-3"><i
                                            class="bi bi-plus-circle me-1"></i> Add Education</button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @if ($showEducationForm)
                                <div class="bg-light p-3 p-md-4 rounded-4 mb-4">
                                    <h6 class="fw-bold mb-3">
                                        {{ $editingEducationId ? 'Edit Education' : 'Add New Education' }}</h6>
                                    <form wire:submit.prevent="saveEducation">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label
                                                    class="form-label fw-semibold small">Institution *</label><input
                                                    type="text" wire:model="educationInstitution"
                                                    class="form-control form-control-sm @error('educationInstitution') is-invalid @enderror"
                                                    placeholder="University name">
                                                @error('educationInstitution')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Degree
                                                    *</label><input type="text" wire:model="educationDegree"
                                                    class="form-control form-control-sm @error('educationDegree') is-invalid @enderror"
                                                    placeholder="e.g. Bachelor of Science">
                                                @error('educationDegree')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Field of
                                                    Study *</label><input type="text" wire:model="educationField"
                                                    class="form-control form-control-sm @error('educationField') is-invalid @enderror"
                                                    placeholder="e.g. Computer Science">
                                                @error('educationField')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Grade
                                                    (Optional)</label><input type="text"
                                                    wire:model="educationGrade" class="form-control form-control-sm"
                                                    placeholder="e.g. 3.8 GPA or A+"></div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">Start
                                                    Date *</label><input type="date"
                                                    wire:model="educationStartDate"
                                                    class="form-control form-control-sm @error('educationStartDate') is-invalid @enderror">
                                                @error('educationStartDate')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6"><label class="form-label fw-semibold small">End
                                                    Date</label><input type="date" wire:model="educationEndDate"
                                                    class="form-control form-control-sm @error('educationEndDate') is-invalid @enderror"
                                                    {{ $educationCurrentlyStudying ? 'disabled' : '' }}>
                                                @error('educationEndDate')
                                                    <div class="invalid-feedback small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check"><input type="checkbox"
                                                        wire:model="educationCurrentlyStudying"
                                                        class="form-check-input" id="currentlyStudying"><label
                                                        class="form-check-label small" for="currentlyStudying">I am
                                                        currently studying here</label></div>
                                            </div>
                                            <div class="col-12"><label
                                                    class="form-label fw-semibold small">Description (Optional)</label>
                                                <textarea wire:model="educationDescription" class="form-control form-control-sm" rows="2"
                                                    placeholder="Brief description of your studies"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap gap-2"><button type="submit"
                                                        class="btn btn-primary rounded-pill px-4 small"><i
                                                            class="bi bi-check-circle me-1"></i>{{ $editingEducationId ? 'Update' : 'Save' }}</button><button
                                                        type="button" wire:click="cancelEducationForm"
                                                        class="btn btn-secondary rounded-pill px-4 small">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                            @if (count($this->educations) > 0)
                                <div class="row g-3">
                                    @foreach ($this->educations as $education)
                                        <div class="col-12">
                                            <div
                                                class="d-flex justify-content-between align-items-start p-3 bg-light rounded-4">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                        <h6 class="fw-bold mb-0">{{ $education['degree'] }}</h6>
                                                        @if ($education['currently_studying'])
                                                            <span
                                                                class="badge bg-success rounded-pill small">Current</span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-1 small"><i
                                                            class="bi bi-building me-1"></i>{{ $education['institution'] }}
                                                        @if ($education['field'])
                                                            <span class="text-muted">·
                                                                {{ $education['field'] }}</span>
                                                        @endif
                                                    </p>
                                                    <div class="d-flex flex-wrap gap-2 mb-1">
                                                        <small class="text-muted"><i
                                                                class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($education['start_date'])->format('M Y') }}
                                                            @if ($education['end_date'])
                                                                -
                                                                {{ \Carbon\Carbon::parse($education['end_date'])->format('M Y') }}
                                                            @else
                                                                - Present
                                                            @endif
                                                        </small>
                                                        @if ($education['grade'])
                                                            <small class="text-muted"><i
                                                                    class="bi bi-star me-1"></i>{{ $education['grade'] }}</small>
                                                        @endif
                                                    </div>
                                                    @if ($education['description'])
                                                        <p class="mb-0 small text-muted">
                                                            {{ $education['description'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-1 flex-shrink-0 ms-2">
                                                    <button wire:click="editEducation({{ $education['id'] }})"
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-2"><i
                                                            class="bi bi-pencil"></i></button>
                                                    <button wire:click="deleteEducation({{ $education['id'] }})"
                                                        wire:confirm="Are you sure you want to delete this education entry?"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-2"><i
                                                            class="bi bi-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4"><i
                                        class="bi bi-mortarboard fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0 small">No education entries added yet.</p>
                                    <p class="text-muted small">Click "Add Education" to get started.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- APPLICATION DETAIL MODAL --}}
    @if (!is_null($this->selectedApplicationId))
        @php $app = collect($this->recentApplications)->firstWhere('id', $this->selectedApplicationId); @endphp
        @if ($app)
            <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
                wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow rounded-4 border-0">
                        <div class="modal-header border-0 p-4">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $app['job_title'] }}</h5>
                            </div>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3">
                                <div class="col-md-6"><small class="text-muted d-block">Type</small><strong
                                        class="small">{{ $app['type'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Applied Date</small><strong
                                        class="small">{{ $app['applied_date'] }}</strong></div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Status</small>
                                    @php $statusColors = ['pending'=>'warning','interview'=>'info','offered'=>'success','rejected'=>'danger']; @endphp
                                    <span
                                        class="badge bg-{{ $statusColors[$app['status']] ?? 'secondary' }} rounded-pill small">{{ ucfirst($app['status']) }}</span>
                                </div>
                                @if ($app['has_interview'] ?? false)
                                    <div class="col-12 mt-3 pt-3 border-top">
                                        <h6 class="fw-bold small text-info"><i
                                                class="bi bi-calendar-event me-1"></i>Interview Scheduled</h6>
                                        <div class="small">
                                            {{ \Carbon\Carbon::parse($app['interview_date'])->format('M d, Y') }} at
                                            {{ $app['interview_time'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- INTERVIEW DETAIL MODAL --}}
    @if (!is_null($this->selectedInterviewId))
        @php $int = collect($this->upcomingInterviews)->firstWhere('id', $this->selectedInterviewId); @endphp
        @if ($int)
            <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
                wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow rounded-4 border-0">
                        <div class="modal-header border-0 p-4">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $int['job_title'] }}</h5>
                            </div>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3">
                                <div class="col-md-6"><small class="text-muted d-block">Interviewer</small><strong
                                        class="small">{{ $int['interviewer'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Date & Time</small><strong
                                        class="small">{{ \Carbon\Carbon::parse($int['date'])->format('M d, Y') }} at
                                        {{ $int['time'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Type</small><strong
                                        class="small">{{ $int['type'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Mode</small><span
                                        class="badge bg-light text-dark small"><i
                                            class="bi bi-{{ $int['mode'] === 'Video Call' ? 'camera-video' : ($int['mode'] === 'In-person' ? 'building' : 'telephone') }} me-1"></i>{{ $int['mode'] }}</span>
                                </div>
                                <div class="col-md-6"><small class="text-muted d-block">Status</small><span
                                        class="badge bg-{{ $int['status'] === 'scheduled' ? 'success' : 'warning' }} rounded-pill small">{{ $int['status'] === 'scheduled' ? 'Confirmed' : 'Pending' }}</span>
                                </div>
                                <div class="col-md-6"><small class="text-muted d-block">Meeting Link</small>
                                    @if ($int['meeting_link'])
                                        <a href="{{ $int['meeting_link'] }}" target="_blank"
                                        class="text-decoration-none small">Click to Join</a>@else<span
                                            class="text-muted small">Not available</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeModal">Close</button>
                            @if ($int['meeting_link'])
                                <button type="button" class="btn btn-primary rounded-pill px-4 small"
                                    wire:click="joinMeeting('{{ $int['meeting_link'] }}')"><i
                                        class="bi bi-camera-video me-1"></i> Join Meeting</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- OFFER DETAIL MODAL (with Withdraw & Decline buttons) --}}
    @if (!is_null($this->selectedOfferId))
        @php $offer = collect($this->offers)->firstWhere('id', $this->selectedOfferId); @endphp
        @if ($offer)
            <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
                wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow rounded-4 border-0">
                        <div class="modal-header border-0 p-4">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $offer['job_title'] }}</h5>
                                <p class="text-muted small mb-0">{{ $offer['company'] }}</p>
                            </div>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3">
                                <div class="col-md-6"><small class="text-muted d-block">Position</small><strong
                                        class="small">{{ $offer['offer_details']['position'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Department</small><strong
                                        class="small">{{ $offer['offer_details']['department'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Salary</small><strong
                                        class="small text-success">{{ $offer['offer_details']['salary'] }}</strong>
                                </div>
                                <div class="col-md-6"><small class="text-muted d-block">Start Date</small><strong
                                        class="small">{{ \Carbon\Carbon::parse($offer['offer_details']['start_date'])->format('M d, Y') }}</strong>
                                </div>
                                <div class="col-md-6"><small class="text-muted d-block">Offer Expiry</small><strong
                                        class="small">{{ \Carbon\Carbon::parse($offer['offer_details']['expiry_date'])->format('M d, Y') }}</strong>
                                </div>
                                <div class="col-md-6"><small class="text-muted d-block">Contact Person</small><strong
                                        class="small">{{ $offer['offer_details']['contact_person'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Contact Email</small><strong
                                        class="small">{{ $offer['offer_details']['contact_email'] }}</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">Offer Letter</small>
                                    @if ($offer['offer_details']['offer_letter'])
                                        <a href="#" class="text-decoration-none small"><i
                                            class="bi bi-file-pdf me-1"></i> Download</a>@else<span
                                            class="text-muted small">Not available</span>
                                    @endif
                                </div>
                                <div class="col-12"><small class="text-muted d-block">Benefits</small>
                                    <p class="small mb-0">{{ $offer['offer_details']['benefits'] }}</p>
                                </div>
                                @if ($offer['offer_details']['additional_notes'] ?? false)
                                    <div class="col-12"><small class="text-muted d-block">Additional Notes</small>
                                        <p class="small mb-0">{{ $offer['offer_details']['additional_notes'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div
                            class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeModal">Close</button>
                            <button type="button" class="btn btn-success rounded-pill px-4 small"><i
                                    class="bi bi-check-circle me-1"></i> Accept Offer</button>
                            <button type="button" class="btn btn-outline-warning rounded-pill px-4 small"
                                wire:click="openNegotiate({{ $offer['id'] }})">
                                <i class="bi bi-currency-dollar me-1"></i> Negotiate
                            </button>
                            {{-- NEW: Withdraw button --}}
                            <button type="button" class="btn btn-outline-danger rounded-pill px-4 small"
                                wire:click="openWithdraw({{ $offer['id'] }})">
                                <i class="bi bi-x-circle me-1"></i> Withdraw
                            </button>
                            {{-- NEW: Decline button --}}
                            <button type="button" class="btn btn-outline-dark rounded-pill px-4 small"
                                wire:click="openDecline({{ $offer['id'] }})">
                                <i class="bi bi-hand-thumbs-down me-1"></i> Decline
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- SALARY NEGOTIATION MODAL --}}
    @if ($showNegotiateModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow rounded-4 border-0">
                    <div class="modal-header border-0 p-4">
                        <h5 class="fw-bold">Salary Negotiation</h5>
                        <button type="button" class="btn-close" wire:click="closeNegotiate"></button>
                    </div>
                    <form wire:submit.prevent="submitNegotiation">
                        <div class="modal-body p-4 pt-0">
                            <p class="text-muted small">Propose a salary amount and add any additional notes for the
                                employer.</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Proposed Salary *</label>
                                <input type="text" wire:model="proposedSalary"
                                    class="form-control form-control-sm @error('proposedSalary') is-invalid @enderror"
                                    placeholder="e.g. 180,000 PKR/month">
                                @error('proposedSalary')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Additional Notes (Optional)</label>
                                <textarea wire:model="negotiationNotes" class="form-control form-control-sm" rows="3"
                                    placeholder="Explain your reasoning or any other details..."></textarea>
                            </div>
                        </div>
                        <div
                            class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeNegotiate">Cancel</button>
                            <button type="submit" class="btn btn-warning rounded-pill px-4 small">
                                <i class="bi bi-send me-1"></i> Submit Negotiation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- NEW: WITHDRAW MODAL --}}
    @if ($showWithdrawModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow rounded-4 border-0">
                    <div class="modal-header border-0 p-4">
                        <h5 class="fw-bold">Withdraw Application</h5>
                        <button type="button" class="btn-close" wire:click="closeWithdraw"></button>
                    </div>
                    <form wire:submit.prevent="submitWithdraw">
                        <div class="modal-body p-4 pt-0">
                            <p class="text-muted small">Please provide a reason for withdrawing your application. This
                                will help us improve our process.</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Reason *</label>
                                <textarea wire:model="withdrawReason"
                                    class="form-control form-control-sm @error('withdrawReason') is-invalid @enderror" rows="4"
                                    placeholder="Explain why you are withdrawing..."></textarea>
                                @error('withdrawReason')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div
                            class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeWithdraw">Cancel</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 small">
                                <i class="bi bi-x-circle me-1"></i> Confirm Withdrawal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- NEW: DECLINE MODAL --}}
    @if ($showDeclineModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow rounded-4 border-0">
                    <div class="modal-header border-0 p-4">
                        <h5 class="fw-bold">Decline Offer</h5>
                        <button type="button" class="btn-close" wire:click="closeDecline"></button>
                    </div>
                    <form wire:submit.prevent="submitDecline">
                        <div class="modal-body p-4 pt-0">
                            <p class="text-muted small">Please provide a reason for declining the offer. This feedback
                                is valuable to us.</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Reason *</label>
                                <textarea wire:model="declineReason"
                                    class="form-control form-control-sm @error('declineReason') is-invalid @enderror" rows="4"
                                    placeholder="Explain why you are declining..."></textarea>
                                @error('declineReason')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div
                            class="modal-footer border-0 p-4 pt-0 justify-content-center justify-content-md-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 small"
                                wire:click="closeDecline">Cancel</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4 small">
                                <i class="bi bi-hand-thumbs-down me-1"></i> Confirm Decline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
