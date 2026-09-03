<?php

use App\Models\Account;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public string $code = '';
    public string $name = '';
    public string $type = '';

    public $parent_id = null;

    public string $normal_balance = '';

    public bool $is_postable = true;
    public bool $is_active = true;

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:accounts,code'],

            'name' => ['required', 'string', 'min:2', 'max:255'],

            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],

            'parent_id' => ['nullable', 'exists:accounts,id'],

            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],

            'is_postable' => ['required', 'boolean'],

            'is_active' => ['required', 'boolean'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Messages
    |--------------------------------------------------------------------------
    */

    protected function messages(): array
    {
        return [
            'code.required' => 'Account code is required.',

            'code.unique' => 'This account code already exists.',

            'code.max' => 'Account code cannot exceed 20 characters.',

            'name.required' => 'Account name is required.',

            'name.min' => 'Account name must contain at least 2 characters.',

            'name.max' => 'Account name cannot exceed 255 characters.',

            'type.required' => 'Account type is required.',

            'type.in' => 'Invalid account type selected.',

            'parent_id.exists' => 'Selected parent account does not exist.',

            'normal_balance.required' => 'Normal balance is required.',

            'normal_balance.in' => 'Invalid normal balance.',

            'is_postable.required' => 'Please select whether this account is postable.',

            'is_active.required' => 'Please select account status.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Property Updated
    |--------------------------------------------------------------------------
    */

    public function updated($property): void
    {
        /*
        |--------------------------------------------------------------------------
        | Account Type Changed
        |--------------------------------------------------------------------------
        |
        | Automatically determine the normal balance.
        |
        | Assets   = Debit
        | Expenses = Debit
        |
        | Liability = Credit
        | Equity    = Credit
        | Income    = Credit
        |
        */

        if ($property === 'type') {
            $this->normal_balance = match ($this->type) {
                'asset', 'expense' => 'debit',

                'liability', 'equity', 'income' => 'credit',

                default => '',
            };

            /*
            |--------------------------------------------------------------------------
            | Reset Parent
            |--------------------------------------------------------------------------
            |
            | When the account type changes, the previously selected
            | parent may no longer be valid.
            |
            */

            $this->parent_id = null;

            /*
            |--------------------------------------------------------------------------
            | Clear Previous Parent Error
            |--------------------------------------------------------------------------
            */

            $this->resetValidation('parent_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Live Validation
        |--------------------------------------------------------------------------
        */

        $this->validateOnly($property);
    }

    /*
    |--------------------------------------------------------------------------
    | Save Account
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $validated = $this->validate();

        /*
        |--------------------------------------------------------------------------
        | Parent Account Validation
        |--------------------------------------------------------------------------
        |
        | A child account must have the same account type as its parent.
        |
        | Correct:
        |
        | Current Assets (Asset)
        |     Inventory (Asset)
        |
        | Wrong:
        |
        | Sales Revenue (Income)
        |     Inventory (Asset)
        |
        */

        if (!empty($this->parent_id)) {
            $parent = Account::find($this->parent_id);

            if (!$parent) {
                $this->addError('parent_id', 'Selected parent account does not exist.');

                return;
            }

            if ($parent->type !== $this->type) {
                $this->addError('parent_id', 'Parent account must belong to the same account type.');

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Child Under Posting Account
            |--------------------------------------------------------------------------
            |
            | A posting account should normally be a leaf account.
            | Therefore another account should not be created underneath it.
            |
            */

            if ((bool) $parent->is_postable) {
                $this->addError('parent_id', 'A posting account cannot be used as a parent account.');

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Account
        |--------------------------------------------------------------------------
        */

        Account::create([
            'code' => trim($validated['code']),

            'name' => trim($validated['name']),

            'type' => $validated['type'],

            'parent_id' => !empty($validated['parent_id']) ? $validated['parent_id'] : null,

            'normal_balance' => $validated['normal_balance'],

            'is_postable' => (bool) $validated['is_postable'],

            'is_active' => (bool) $validated['is_active'],

            /*
            |--------------------------------------------------------------------------
            | User-created Account
            |--------------------------------------------------------------------------
            */

            'is_system' => false,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset Form
        |--------------------------------------------------------------------------
        */

        $this->reset(['code', 'name', 'type', 'parent_id', 'normal_balance']);

        $this->is_postable = true;
        $this->is_active = true;

        $this->resetValidation();

        session()->flash('success', 'Chart of Account added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Data For View
    |--------------------------------------------------------------------------
    */

    public function with(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Parent Accounts
        |--------------------------------------------------------------------------
        |
        | Only:
        |
        | 1. Active accounts
        | 2. Same account type
        | 3. Non-postable/group accounts
        |
        | can become parent accounts.
        |
        */

        $parentAccounts = Account::query()

            ->when(
                $this->type,

                fn($query) => $query->where('type', $this->type),
            )

            ->where('is_active', true)

            ->where('is_postable', false)

            ->orderBy('code')

            ->get();

        return [
            'parentAccounts' => $parentAccounts,
        ];
    }
};

?>

<div>

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Add Chart of Account
            </h3>

            <p class="text-muted mb-0">
                Create a new account for your Chart of Accounts
            </p>

        </div>

        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary" wire:navigate>
            <i class="bi bi-arrow-left me-1"></i>
            Back to Accounts
        </a>

    </div>


    {{-- ================================================================
        SUCCESS MESSAGE
    ================================================================= --}}

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>
    @endif


    {{-- ================================================================
        ACCOUNT FORM
    ================================================================= --}}

    <div class="row">

        <div class="col-xl-8 col-lg-10">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary text-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-journal-plus me-2"></i>

                        Account Information

                    </h5>

                </div>


                <div class="card-body p-4">

                    <form wire:submit="save">


                        {{-- ==================================================
                            ACCOUNT CODE
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Account Code

                                <span class="text-danger">*</span>

                            </label>


                            <input type="text"
                                class="form-control
                                    @error('code')
                                        is-invalid
                                    @enderror"
                                placeholder="Example: 1100" wire:model.blur="code">


                            @error('code')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror


                            <small class="text-muted">

                                Use a unique accounting code,
                                for example 1100, 1200 or 4100.

                            </small>

                        </div>


                        {{-- ==================================================
                            ACCOUNT NAME
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Account Name

                                <span class="text-danger">*</span>

                            </label>


                            <input type="text"
                                class="form-control
                                    @error('name')
                                        is-invalid
                                    @enderror"
                                placeholder="Example: Cash in Hand" wire:model.blur="name">


                            @error('name')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>


                        {{-- ==================================================
                            ACCOUNT TYPE
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Account Type

                                <span class="text-danger">*</span>

                            </label>


                            <select
                                class="form-select
                                    @error('type')
                                        is-invalid
                                    @enderror"
                                wire:model.live="type">

                                <option value="">
                                    Select Account Type
                                </option>

                                <option value="asset">
                                    Asset
                                </option>

                                <option value="liability">
                                    Liability
                                </option>

                                <option value="equity">
                                    Equity
                                </option>

                                <option value="income">
                                    Income
                                </option>

                                <option value="expense">
                                    Expense
                                </option>

                            </select>


                            @error('type')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>


                        {{-- ==================================================
                            PARENT ACCOUNT
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Parent Account

                            </label>


                            <select
                                class="form-select
                                    @error('parent_id')
                                        is-invalid
                                    @enderror"
                                wire:model="parent_id" @disabled(empty($type))>

                                <option value="">

                                    @if (empty($type))
                                        Select account type first
                                    @else
                                        No Parent / Root Account
                                    @endif

                                </option>


                                @foreach ($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}">

                                        {{ $parent->code }}
                                        -
                                        {{ $parent->name }}

                                    </option>
                                @endforeach

                            </select>


                            @error('parent_id')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror


                            <small class="text-muted">

                                Only active group accounts of the
                                selected account type are displayed.

                            </small>

                        </div>


                        {{-- ==================================================
                            NORMAL BALANCE
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Normal Balance

                            </label>


                            <input type="text"
                                class="form-control
                                    @error('normal_balance')
                                        is-invalid
                                    @enderror"
                                value="{{ $normal_balance ? ucfirst($normal_balance) : '' }}"
                                placeholder="Automatically determined" readonly>


                            @error('normal_balance')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror


                            <small class="text-muted">

                                Assets and Expenses normally have
                                Debit balances.

                                Liabilities, Equity and Income normally
                                have Credit balances.

                            </small>

                        </div>


                        {{-- ==================================================
                            POSTING ACCOUNT
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Account Purpose

                                <span class="text-danger">*</span>

                            </label>


                            <select
                                class="form-select
                                    @error('is_postable')
                                        is-invalid
                                    @enderror"
                                wire:model="is_postable">

                                <option value="1">

                                    Posting Account

                                </option>

                                <option value="0">

                                    Group / Heading Account

                                </option>

                            </select>


                            @error('is_postable')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror


                            <small class="text-muted">

                                Posting accounts receive journal entries.

                                Group accounts organize other accounts
                                and cannot receive journal entries directly.

                            </small>

                        </div>


                        {{-- ==================================================
                            STATUS
                        =================================================== --}}

                        <div class="mb-4">

                            <label class="form-label fw-semibold">

                                Status

                                <span class="text-danger">*</span>

                            </label>


                            <select
                                class="form-select
                                    @error('is_active')
                                        is-invalid
                                    @enderror"
                                wire:model="is_active">

                                <option value="1">
                                    Active
                                </option>

                                <option value="0">
                                    Inactive
                                </option>

                            </select>


                            @error('is_active')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>


                        {{-- ==================================================
                            ACTION BUTTONS
                        =================================================== --}}

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">

                            <a href="{{ route('accounts.index') }}" class="btn btn-light" wire:navigate>
                                Cancel
                            </a>


                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                wire:target="save" @disabled(empty($code) || empty($name) || empty($type))>


                                <span wire:loading.remove wire:target="save">

                                    <i class="bi bi-check-lg me-1"></i>

                                    Save Account

                                </span>


                                <span wire:loading wire:target="save">

                                    <span class="spinner-border spinner-border-sm me-1"></span>

                                    Saving...

                                </span>

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>


        {{-- ================================================================
            ACCOUNTING HELP
        ================================================================= --}}

        <div class="col-xl-4 col-lg-10 mt-4 mt-xl-0">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6 class="fw-bold">

                        <i class="bi bi-info-circle me-1"></i>

                        Account Types

                    </h6>

                    <hr>


                    <div class="mb-3">

                        <strong>Asset</strong>

                        <div class="small text-muted">
                            Cash, Bank, Inventory, Receivables,
                            Equipment
                        </div>

                    </div>


                    <div class="mb-3">

                        <strong>Liability</strong>

                        <div class="small text-muted">
                            Accounts Payable, Loans, Taxes Payable
                        </div>

                    </div>


                    <div class="mb-3">

                        <strong>Equity</strong>

                        <div class="small text-muted">
                            Owner Capital, Retained Earnings
                        </div>

                    </div>


                    <div class="mb-3">

                        <strong>Income</strong>

                        <div class="small text-muted">
                            Sales Revenue, Service Revenue
                        </div>

                    </div>


                    <div>

                        <strong>Expense</strong>

                        <div class="small text-muted">
                            Salaries, Rent, Utilities, Advertising
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
