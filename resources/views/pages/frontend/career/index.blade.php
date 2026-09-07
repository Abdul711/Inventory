<?php

use Livewire\Component;
use App\Models\JobPosting;
use App\Models\Department;
use App\Models\SavedJob;
new class extends Component {
    public string $search = '';
    public $openFaq = 1;
    public array $departments = [];
    public array $jobs = [];
    public array $benefits = [];
    public array $hiringProcess = [];
    public array $testimonials = [];
    public array $faqs = [];
    public function toggleFaq($id): void
    {
        $id = $id;

        $this->openFaq = $this->openFaq == $id ? null : $id;
    }
    public function mount(): void
    {
        $this->departments = Department::withCount(['job'])
            ->get()
            ->toArray();

        $this->jobs = [
            [
                'title' => 'Senior Laravel Developer',
                'department' => 'Engineering',
                'location' => 'Karachi',
                'type' => 'Full Time',
                'experience' => '3+ Years',
                'salary' => 'PKR 180,000',
            ],
            [
                'title' => 'React Developer',
                'department' => 'Engineering',
                'location' => 'Remote',
                'type' => 'Remote',
                'experience' => '2+ Years',
                'salary' => 'PKR 220,000',
            ],
            [
                'title' => 'HR Executive',
                'department' => 'Human Resources',
                'location' => 'Karachi',
                'type' => 'Full Time',
                'experience' => '1+ Year',
                'salary' => 'PKR 90,000',
            ],
        ];
        $this->jobs = JobPosting::with(['creator', 'department', 'designation'])
            ->get()
            ->toArray();
        $this->benefits = ['Competitive Salary', 'Medical Insurance', 'Annual Bonus', 'Paid Leave', 'Learning Budget', 'Flexible Working Hours'];

        $this->hiringProcess = ['Application', 'HR Screening', 'Technical Interview', 'Final Interview', 'Offer Letter'];
        $this->testimonials = [
            [
                'name' => 'Muhammad Ali',
                'designation' => 'Senior Software Engineer',
                'message' => 'Amazing place to grow professionally.',
            ],
        ];

        $this->faqs = [
            [
                'question' => 'Can I apply for multiple positions?',
                'answer' => 'Yes, you may apply for multiple openings.',
            ],
        ];
    }
    public function savedJob($id)
    {
        $user = SavedJob::firstOrCreate([
            'job_posting_id' => $id,
            'applicant_id' => auth('applicant')->user()->id,
        ]);
    }
    public function applyJob($job_id)
    {
        return $this->redirectRoute('jobdetail', [
            'id' => $job_id,
        ]);
    }
    public function getFilteredJobsProperty()
    {
        if ($this->search == '') {
            return $this->jobs;
        }

        return array_filter($this->jobs, function ($job) {
            return str_contains(strtolower($job['title'] . ' ' . $job['department'] . ' ' . $job['location']), strtolower($this->search));
        });
    }
    public function rendering($view): void
    {
        $view->layout('components.layouts.ecommerce', [
            'cartCount' => 0,
        ]);
    }
};

?>

<div class="career-page">

    <style>
        body {
            background: #f8fafc;
        }

        .hero {

            background: linear-gradient(135deg, #0f172a, #2563eb);
            color: white;
            border-radius: 35px;
            overflow: hidden;

        }

        .hero h1 {

            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 900;

        }

        .hero p {

            color: #dbeafe;
            font-size: 1.1rem;

        }

        .search-box {

            border-radius: 50px;
            padding: 15px 20px;
            border: none;

        }

        .hero-stat h2 {

            font-size: 2rem;
            font-weight: 800;

        }

        .hero-stat small {

            color: #cbd5e1;

        }

        .about-card {

            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);

        }

        .section-title {

            font-weight: 800;
            color: #0f172a;

        }

        .department-card {
            border: none;
            border-radius: 25px;
            transition: .3s;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            height: 100%;
        }

        .department-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .15);
        }

        .department-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            background: #eff6ff;
            color: #2563eb;
            font-size: 28px;
        }

        .job-card {

            border: none;
            border-radius: 25px;
            transition: .3s;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .08);

        }

        .job-card:hover {

            transform: translateY(-8px);

        }

        .job-badge {

            border-radius: 50px;
            font-weight: 600;

        }

        .job-info {

            color: #64748b;
            font-size: 15px;

        }

        .salary {

            color: #2563eb;
            font-size: 22px;
            font-weight: 800;

        }

        .apply-btn {

            border-radius: 50px;

        }

        .benefit-card {
            border: none;
            border-radius: 25px;
            background: #fff;
            transition: .3s;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .06);
        }

        .benefit-card:hover {
            transform: translateY(-8px);
        }

        .benefit-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: auto;
        }

        .process-card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .06);
            position: relative;
        }

        .process-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            margin: auto;
        }

        .testimonial-card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .06);
        }

        .cta {
            background: linear-gradient(135deg, #0f172a, #2563eb);
            border-radius: 35px;
            color: #fff;
        }

        .accordion-item {
            border: none;
            border-radius: 20px !important;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
        }

        .accordion-button {
            font-weight: 700;
        }

        @media(max-width:768px) {

            .hero {

                text-align: center;
                border-radius: 20px;

            }

        }
    </style>

    <div class="container py-5">

        <!-- HERO -->

        <section class="hero p-4 p-lg-5 mb-5">

            <div class="row align-items-center">

                <div class="col-lg-12">

                    <span class="badge bg-light text-primary rounded-pill px-3 py-2 mb-3">

                        WE'RE HIRING

                    </span>

                    <h1>

                        Build Your Career With Us

                    </h1>

                    <p class="mt-3">

                        Join passionate people building modern ERP, CRM,
                        HRM and E-Commerce solutions used by thousands
                        of businesses worldwide.

                    </p>

                    <div class="bg-white rounded-pill p-2 d-flex mt-4 shadow">

                        <input type="text" class="form-control search-box" wire:model.live.debounce.500ms="search"
                            placeholder="Search by job title, department or location...">

                        <button class="btn btn-primary rounded-pill px-4">

                            Search

                        </button>

                    </div>

                    <div class="row mt-5 text-center">

                        <div class="col-4 hero-stat">

                            <h2>25</h2>

                            <small>Open Jobs</small>

                        </div>

                        <div class="col-4 hero-stat">

                            <h2>150+</h2>

                            <small>Employees</small>

                        </div>

                        <div class="col-4 hero-stat">

                            <h2>2018</h2>

                            <small>Founded</small>

                        </div>

                    </div>

                </div>



            </div>

        </section>

        <!-- ABOUT -->

        <section class="mb-5">

            <div class="about-card p-5">

                <div class="row align-items-center">

                    <div class="col-lg-12">

                        <span class="badge bg-primary mb-3">

                            ABOUT US

                        </span>

                        <h2 class="section-title mb-4">

                            Work With An Amazing Team

                        </h2>

                        <p class="text-muted">

                            We are a fast-growing eCommerce company dedicated to providing customers with high-quality
                            products, competitive prices, and a seamless online shopping experience. From product
                            sourcing and inventory management to order fulfillment and customer support, we strive to
                            deliver excellence at every step of the customer journey.

                            Our workplace is built on teamwork, integrity, innovation, and continuous improvement.
                            Whether you're starting your career or bringing years of experience, you'll have the
                            opportunity to grow, develop new skills, and make a meaningful impact while helping us serve
                            customers and expand our business.

                        </p>

                        <p class="text-muted">

                            Whether you're an experienced professional or just beginning your career, you'll work with
                            dedicated teams across sales, inventory, warehousing, order management, customer support,
                            and logistics to ensure customers receive the best shopping experience.
                        </p>

                    </div>



                </div>

            </div>

        </section>

        <!-- Part 2 starts below -->
        <section class="mb-5">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="section-title mb-1">
                        Departments
                    </h2>

                    <p class="text-muted mb-0">
                        Explore opportunities across different teams.
                    </p>

                </div>

            </div>

            <div class="row g-4">

                @foreach ($departments as $department)
                    <div class="col-md-6 col-lg-4">

                        <div class="department-card p-4 text-center">


                            <h5 class="fw-bold">

                                {{ $department['name'] }}

                            </h5>

                            <p class="text-muted">

                                {{ $department['job_count'] }}
                                Open Positions

                            </p>


                        </div>

                    </div>
                @endforeach

            </div>

        </section>


        {{-- =========================
CURRENT OPENINGS
========================= --}}

        <section class="mb-5">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="section-title mb-1">

                        Current Open Positions

                    </h2>

                    <p class="text-muted mb-0">

                        {{ count($this->filteredJobs) }}
                        jobs found

                    </p>

                </div>

            </div>

            <div class="row g-4">

                @forelse($this->filteredJobs as $job)
                    <div class="col-lg-4">

                        <div class="job-card p-4 h-100">

                            <div class="d-flex justify-content-between mb-3">

                                <span class="badge bg-primary job-badge">

                                    {{ $job['employment_type'] }}

                                </span>

                                <span class="badge bg-success job-badge">

                                    Hiring

                                </span>

                            </div>

                            <h4 class="fw-bold">

                                {{ $job['designation']['name'] }}

                            </h4>

                            <div class="job-info mt-3">

                                <div class="mb-2">

                                    <i class="bi bi-building me-2"></i>

                                    {{ $job['department']['name'] }}

                                </div>


                                <div class="mb-2">

                                    <i class="bi bi-briefcase me-2"></i>

                                    {{ $job['min_experience'] }}

                                </div>

                            </div>

                            <div class="salary mt-4">

                                {{ $job['minimum_salary'] }} -
                                {{ $job['maximum_salary'] }}

                            </div>

                            <div class="mt-4 d-grid">



                                @if (auth('applicant')->check())
                                    <button class="btn btn-primary apply-btn"
                                        wire:click="savedJob({{ $job['id'] }})">
                                        <i class="bi bi-bookmark-fill me-2"></i>

                                        Save Job

                                    </button>
                                @endif
                                <button class="btn btn-primary apply-btn" wire:click="applyJob({{ $job['id'] }})">

                                    <i class="bi bi-send me-2"></i>

                                    Apply Now

                                </button>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-12">

                        <div class="alert alert-info">

                            <i class="bi bi-search me-2"></i>

                            No job openings matched your search.

                        </div>

                    </div>
                @endforelse

            </div>

        </section>
        <section class="mb-5">

            <h2 class="section-title text-center mb-2">
                Why Join Us
            </h2>

            <p class="text-center text-muted mb-5">
                We invest in our people and their future.
            </p>

            <div class="row g-4">

                @php

                    $icons = ['bi-cash-stack', 'bi-heart-pulse', 'bi-airplane', 'bi-laptop', 'bi-book', 'bi-award'];

                @endphp

                @foreach ($benefits as $benefit)
                    <div class="col-md-6 col-lg-4">

                        <div class="benefit-card p-4 text-center h-100">

                            <div class="benefit-icon mb-3">

                                <i class="bi {{ $icons[$loop->index] ?? 'bi-check-circle' }}"></i>

                            </div>

                            <h5 class="fw-bold">

                                {{ $benefit }}

                            </h5>

                        </div>

                    </div>
                @endforeach

            </div>

        </section>


        {{-- ===========================================
HIRING PROCESS
=========================================== --}}

        <section class="mb-5">

            <h2 class="section-title text-center mb-5">

                Hiring Process

            </h2>

            <div class="row g-4">

                @foreach ($hiringProcess as $step)
                    <div class="col-md">

                        <div class="process-card p-4 text-center h-100">

                            <div class="process-number">

                                {{ $loop->iteration }}

                            </div>

                            <h5 class="fw-bold mt-4">

                                {{ $step }}

                            </h5>

                        </div>

                    </div>
                @endforeach

            </div>

        </section>


        {{-- ===========================================
EMPLOYEE TESTIMONIALS
=========================================== --}}

        <section class="mb-5">

            <h2 class="section-title text-center mb-5">

                Employee Stories

            </h2>

            <div class="row g-4">

                @foreach ($testimonials as $testimonial)
                    <div class="col-lg-6">

                        <div class="testimonial-card p-4 h-100">

                            <div class="text-warning fs-4">

                                ★★★★★

                            </div>

                            <p class="my-4 text-muted">

                                "{{ $testimonial['message'] }}"

                            </p>

                            <div class="d-flex align-items-center">

                                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
                                    style="width:60px;height:60px;">

                                    {{ strtoupper(substr($testimonial['name'], 0, 1)) }}

                                </div>

                                <div class="ms-3">

                                    <h5 class="mb-0">

                                        {{ $testimonial['name'] }}

                                    </h5>

                                    <small class="text-muted">

                                        {{ $testimonial['designation'] }}

                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </section>


        {{-- ===========================================
FAQ
=========================================== --}}

        <section class="mb-5">

            <h2 class="section-title text-center mb-5">

                Frequently Asked Questions

            </h2>

            <div class="accordion">

                @foreach ($faqs as $faq)
                    <div class="card mb-3 border-0 shadow-sm rounded-4">

                        <button type="button" wire:click="toggleFaq({{ $loop->iteration }})"
                            class="btn text-start p-4 fw-bold w-100">
                            {{ $faq['question'] }}
                        </button>

                        @if ($openFaq == $loop->iteration)
                            <div class="px-4 pb-4">

                                {{ $faq['answer'] }}

                            </div>
                        @endif

                    </div>
                @endforeach

            </div>

        </section>


        {{-- ===========================================
CALL TO ACTION
=========================================== --}}

        <section class="cta p-5 text-center">

            <span class="badge bg-light text-primary rounded-pill px-3 py-2 mb-3">

                JOIN OUR TEAM

            </span>

            <h2 class="display-5 fw-bold">

                Ready To Start Your Career?

            </h2>

            <p class="lead text-light">

                Discover exciting opportunities and become part of a team
                that values innovation, collaboration and continuous growth.

            </p>

            <div class="mt-4">

                <button class="btn btn-light btn-lg rounded-pill px-5">

                    <i class="bi bi-send-fill me-2"></i>

                    Apply Today

                </button>

            </div>

        </section>

    </div>

</div>

</div>
