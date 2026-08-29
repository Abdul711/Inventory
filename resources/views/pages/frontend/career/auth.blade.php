<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public bool $isLogin = true;

    // Login fields
    public string $login_email = '';
    public string $login_password = '';

    // Registration fields
    public string $reg_full_name = '';
    public string $reg_email = '';
    public string $reg_phone = '';
    public string $reg_password = '';
    public string $reg_password_confirmation = '';
    public string $reg_father_name = '';
    public $reg_photo = null;
    public string $reg_martial_status = 'single';
    public string $reg_cnic = '';
    public string $reg_date_of_birth = '';
    public string $reg_address = '';
    public string $reg_gender = 'female';
    public string $reg_bio = '';
    public string $reg_linkedin = '';

    public function switchMode(string $mode): void
    {
        if (!in_array($mode, ['login', 'register'], true)) {
            return;
        }

        $this->isLogin = $mode === 'login';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updated($property)
    {
        try {
            if (str_contains($property, 'reg_full_name')) {
                $this->reg_linkedin = 'https://www.linkedin.com/in/' . str()->slug($this->reg_full_name);
            }
        } catch (Exception $e) {
        }
    }

    public function login()
    {
        $validated = $this->validate([
            'login_email' => ['required', 'email'],
            'login_password' => ['required', 'string', 'min:6'],
        ]);

        $credentials = [
            'email' => strtolower(trim($validated['login_email'])),
            'password' => $validated['login_password'],
        ];

        if (!Auth::guard('applicant')->attempt($credentials)) {
            $this->addError('login_email', 'The provided email address or password is incorrect.');

            return null;
        }

        request()->session()->regenerate();

        session()->flash('success', 'Welcome back!');

        return redirect()->route('applicantportal');
    }

    public function register()
    {
        $validated = $this->validate(
            [
                'reg_full_name' => ['required', 'string', 'min:3', 'max:255', 'unique:applicants,full_name'],
                'reg_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'reg_phone' => ['required', 'string', 'max:255'],
                'reg_password' => ['required', 'confirmed', Password::min(8)],
                'reg_father_name' => ['required', 'string', 'max:255'],
                'reg_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'reg_martial_status' => ['required', 'in:single,married,divorced'],
                'reg_cnic' => ['required', 'regex:/^\d{5}-\d{7}-\d$/', 'max:255'],
                'reg_date_of_birth' => ['required', 'date', 'before:today'],
                'reg_address' => ['nullable', 'string', 'max:5000'],
                'reg_gender' => ['required', 'in:male,female'],
                'reg_bio' => ['nullable', 'string', 'max:5000'],
                'reg_linkedin' => ['required', 'url', 'max:255'],
            ],
            [
                'reg_cnic.regex' => 'CNIC must use the format 42101-1234567-1.',
                'reg_photo.max' => 'The profile photo cannot be larger than 2 MB.',
                'reg_linkedin.url' => 'Please enter a valid LinkedIn profile URL.',
            ],
        );

        $photoPath = null;

        if ($this->reg_photo) {
            $fileName = 'applicant-' . time() . '-' . str()->random(8) . '.' . $this->reg_photo->getClientOriginalExtension();
            $photoPath = $this->reg_photo->storeAs('applicant', 'public');
        }

        $user = Applicant::create([
            'full_name' => trim($validated['reg_full_name']),
            'email' => strtolower(trim($validated['reg_email'])),
            'phone' => filled($validated['reg_phone']) ? trim($validated['reg_phone']) : null,
            'password' => Hash::make($validated['reg_password']),
            'father_name' => filled($validated['reg_father_name']) ? trim($validated['reg_father_name']) : null,
            'photo' => $fileName,
            'martial_status' => $validated['reg_martial_status'],
            'cnic' => filled($validated['reg_cnic']) ? trim($validated['reg_cnic']) : null,
            'date_of_birth' => $validated['reg_date_of_birth'] ?: null,
            'address' => filled($validated['reg_address']) ? trim($validated['reg_address']) : null,
            'gender' => $validated['reg_gender'],
            'bio' => filled($validated['reg_bio']) ? trim($validated['reg_bio']) : null,
            'linkedin' => trim($validated['reg_linkedin']),
        ]);

        Auth::guard('applicant')->login($user);
        request()->session()->regenerate();

        session()->flash('success', 'Registration successful! Welcome to the Applicant Portal.');

        return redirect()->route('applicantportal');
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
    <div class="py-4 mb-4 shadow-sm">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3 gap-lg-2">
                <div class="text-center text-lg-start d-flex flex-column align-items-center align-items-lg-start">
                    <h4 class="mb-0 fw-bold fs-4 fs-md-3 fs-lg-2">
                        {{ $isLogin ? 'Applicant Login' : 'Create Account' }}
                    </h4>

                    <small class="text-muted fw-light mt-1 d-none d-sm-block">
                        {{ $isLogin ? 'Access your job applications and interviews' : 'Start your journey to your dream job' }}
                    </small>
                </div>

                <button type="button" onclick="window.history.back()"
                    class="btn btn-light btn-sm rounded-pill px-3 px-md-4 fw-medium shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </button>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="{{ $isLogin ? 'col-md-7 col-lg-5' : 'col-lg-9' }}">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-body p-4 p-md-5">
                        @if (session()->has('success'))
                            <div class="alert alert-success rounded-4 mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger rounded-4 mb-4">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Login form --}}
                        @if ($isLogin)
                            <form wire:submit.prevent="login">
                                <div class="mb-3">
                                    <label for="login_email" class="form-label fw-semibold small">
                                        Email Address
                                    </label>

                                    <input wire:model.blur="login_email" type="email" id="login_email"
                                        autocomplete="email"
                                        class="form-control form-control-lg rounded-3 @error('login_email') is-invalid @enderror"
                                        placeholder="john@example.com" required>

                                    @error('login_email')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="login_password" class="form-label fw-semibold small">
                                        Password
                                    </label>

                                    <input wire:model="login_password" type="password" id="login_password"
                                        autocomplete="current-password"
                                        class="form-control form-control-lg rounded-3 @error('login_password') is-invalid @enderror"
                                        placeholder="••••••••" required>

                                    @error('login_password')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <button type="submit" wire:loading.attr="disabled" wire:target="login"
                                    class="btn btn-primary w-100 rounded-pill py-2 fw-bold mb-3">
                                    <span wire:loading.remove wire:target="login">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Login
                                    </span>

                                    <span wire:loading wire:target="login">
                                        Logging in...
                                    </span>
                                </button>

                                <div class="text-center small">
                                    Don't have an account?

                                    <a href="#" wire:click.prevent="switchMode('register')"
                                        class="text-decoration-none fw-bold">
                                        Register here
                                    </a>
                                </div>
                            </form>
                        @endif

                        {{-- Registration form --}}
                        @if (!$isLogin)
                            <form wire:submit.prevent="register" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="reg_full_name" class="form-label fw-semibold small">
                                            Full Name
                                        </label>

                                        <input wire:model.live="reg_full_name" type="text" id="reg_full_name"
                                            class="form-control form-control-lg rounded-3 @error('reg_full_name') is-invalid @enderror"
                                            placeholder="John Doe" required>

                                        @error('reg_full_name')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_father_name" class="form-label fw-semibold small">
                                            Father Name
                                        </label>

                                        <input wire:model="reg_father_name" type="text" id="reg_father_name"
                                            class="form-control form-control-lg rounded-3 @error('reg_father_name') is-invalid @enderror"
                                            placeholder="Enter father name">

                                        @error('reg_father_name')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_email" class="form-label fw-semibold small">
                                            Email Address
                                        </label>

                                        <input wire:model="reg_email" type="email" id="reg_email" autocomplete="email"
                                            class="form-control form-control-lg rounded-3 @error('reg_email') is-invalid @enderror"
                                            placeholder="john@example.com" required>

                                        @error('reg_email')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_phone" class="form-label fw-semibold small">
                                            Phone Number
                                        </label>

                                        <input wire:model="reg_phone" type="tel" id="reg_phone" autocomplete="tel"
                                            class="form-control form-control-lg rounded-3 @error('reg_phone') is-invalid @enderror"
                                            placeholder="+92 300 1234567">

                                        @error('reg_phone')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_password" class="form-label fw-semibold small">
                                            Password
                                        </label>

                                        <input wire:model="reg_password" type="password" id="reg_password"
                                            autocomplete="new-password"
                                            class="form-control form-control-lg rounded-3 @error('reg_password') is-invalid @enderror"
                                            placeholder="••••••••" required>

                                        @error('reg_password')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_password_confirmation" class="form-label fw-semibold small">
                                            Confirm Password
                                        </label>

                                        <input wire:model="reg_password_confirmation" type="password"
                                            id="reg_password_confirmation" autocomplete="new-password"
                                            class="form-control form-control-lg rounded-3" placeholder="••••••••"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_martial_status" class="form-label fw-semibold small">
                                            Marital Status
                                        </label>

                                        <select wire:model="reg_martial_status" id="reg_martial_status"
                                            class="form-select form-select-lg rounded-3 @error('reg_martial_status') is-invalid @enderror"
                                            required>
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                            <option value="divorced">Divorced</option>
                                        </select>

                                        @error('reg_martial_status')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_gender" class="form-label fw-semibold small">
                                            Gender
                                        </label>

                                        <select wire:model="reg_gender" id="reg_gender"
                                            class="form-select form-select-lg rounded-3 @error('reg_gender') is-invalid @enderror"
                                            required>
                                            <option value="female">Female</option>
                                            <option value="male">Male</option>
                                        </select>

                                        @error('reg_gender')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_cnic" class="form-label fw-semibold small">
                                            CNIC
                                        </label>

                                        <input wire:model="reg_cnic" type="text" id="reg_cnic"
                                            class="form-control form-control-lg rounded-3 @error('reg_cnic') is-invalid @enderror"
                                            placeholder="42101-1234567-1">

                                        @error('reg_cnic')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="reg_date_of_birth" class="form-label fw-semibold small">
                                            Date of Birth
                                        </label>

                                        <input wire:model="reg_date_of_birth" type="date" id="reg_date_of_birth"
                                            max="{{ now()->subDay()->format('Y-m-d') }}"
                                            class="form-control form-control-lg rounded-3 @error('reg_date_of_birth') is-invalid @enderror">

                                        @error('reg_date_of_birth')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="reg_photo" class="form-label fw-semibold small">
                                            Profile Photo
                                        </label>

                                        <input wire:model="reg_photo" type="file" id="reg_photo"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="form-control form-control-lg rounded-3 @error('reg_photo') is-invalid @enderror">

                                        @error('reg_photo')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        <div wire:loading wire:target="reg_photo" class="text-primary small mt-2">
                                            Uploading photo...
                                        </div>

                                        @if ($reg_photo)
                                            <div class="mt-3">
                                                <img src="{{ $reg_photo->temporaryUrl() }}"
                                                    alt="Profile photo preview" width="100" height="100"
                                                    class="rounded-circle border" style="object-fit: cover;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-12">
                                        <label for="reg_address" class="form-label fw-semibold small">
                                            Address
                                        </label>

                                        <textarea wire:model="reg_address" id="reg_address" rows="3"
                                            class="form-control rounded-3 @error('reg_address') is-invalid @enderror"
                                            placeholder="Enter your complete address"></textarea>

                                        @error('reg_address')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="reg_bio" class="form-label fw-semibold small">
                                            Professional Bio
                                        </label>

                                        <textarea wire:model="reg_bio" id="reg_bio" rows="4"
                                            class="form-control rounded-3 @error('reg_bio') is-invalid @enderror"
                                            placeholder="Write a short professional bio"></textarea>

                                        @error('reg_bio')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="reg_linkedin" class="form-label fw-semibold small">
                                            LinkedIn Profile
                                        </label>

                                        <input wire:model="reg_linkedin" type="url" id="reg_linkedin"
                                            class="form-control form-control-lg rounded-3 @error('reg_linkedin') is-invalid @enderror"
                                            placeholder="https://www.linkedin.com/in/username" required>

                                        @error('reg_linkedin')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" wire:loading.attr="disabled" wire:target="register,reg_photo"
                                    class="btn btn-primary w-100 rounded-pill py-2 fw-bold mt-4 mb-3">
                                    <span wire:loading.remove wire:target="register">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Register
                                    </span>

                                    <span wire:loading wire:target="register">
                                        Registering...
                                    </span>
                                </button>

                                <div class="text-center small">
                                    Already have an account?

                                    <a href="#" wire:click.prevent="switchMode('login')"
                                        class="text-decoration-none fw-bold">
                                        Login here
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
