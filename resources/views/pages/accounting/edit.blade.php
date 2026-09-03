<?php

use App\Models\Account;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public $account_id;

    public $code = '';
    public $name = '';
    public $type = '';
    public $parent_id = null;
    public $normal_balance = '';
    public $is_postable = 1;
    public $is_active = 1;

    public function mount($id): void
    {
        $account = Account::findOrFail($id);

        $this->account_id = $account->id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->parent_id = $account->parent_id;
        $this->normal_balance = $account->normal_balance;
        $this->is_postable = (int) $account->is_postable;
        $this->is_active = (int) $account->is_active;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'max:20', Rule::unique('accounts', 'code')->ignore($this->account_id)],

            'name' => ['required', 'min:2', 'max:255'],

            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],

            'parent_id' => ['nullable', 'exists:accounts,id', Rule::notIn([$this->account_id])],

            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],

            'is_postable' => ['required', 'boolean'],

            'is_active' => ['required', 'boolean'],
        ];
    }

    protected $messages = [
        'code.required' => 'Account code is required.',
        'code.unique' => 'This account code already exists.',

        'name.required' => 'Account name is required.',
        'name.min' => 'Account name must contain at least 2 characters.',

        'type.required' => 'Account type is required.',
        'type.in' => 'Invalid account type selected.',

        'parent_id.exists' => 'Selected parent account does not exist.',
        'parent_id.not_in' => 'An account cannot be its own parent.',

        'normal_balance.required' => 'Normal balance is required.',
    ];

    public function updated($property): void
    {
        if ($property === 'type') {
            $this->normal_balance = match ($this->type) {
                'asset', 'expense' => 'debit',

                'liability', 'equity', 'income' => 'credit',

                default => '',
            };

            $this->parent_id = null;

            $this->resetValidation('parent_id');
        }

        $this->validateOnly($property);
    }

    public function update(): void
    {
        $this->validate();

        $account = Account::findOrFail($this->account_id);

        if ($this->parent_id) {
            $parent = Account::findOrFail($this->parent_id);

            if ($parent->type !== $this->type) {
                $this->addError('parent_id', 'Parent account must belong to the same account type.');

                return;
            }

            if ((int) $parent->is_postable === 1) {
                $this->addError('parent_id', 'A posting account cannot be used as a parent account.');

                return;
            }
        }

        $account->update([
            'code' => trim($this->code),

            'name' => trim($this->name),

            'type' => $this->type,

            'parent_id' => $this->parent_id ?: null,

            'normal_balance' => $this->normal_balance,

            'is_postable' => (int) $this->is_postable,

            'is_active' => (int) $this->is_active,
        ]);

        session()->flash('success', 'Account updated successfully.');
    }

    public function with(): array
    {
        return [
            'parentAccounts' => Account::query()

                ->where('id', '!=', $this->account_id)

                ->when(
                    $this->type,

                    fn($query) => $query->where('type', $this->type),
                )

                ->where('is_active', 1)

                ->where('is_postable', 0)

                ->orderBy('code')

                ->get(),
        ];
    }
};

?>

<div>

    <div class="row">

        <div class="col-lg-12">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white">

                    <h4 class="mb-0">
                        Edit Chart of Account
                    </h4>

                </div>

                <div class="card-body">

                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">

                            {{ session('success') }}

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                        </div>
                    @endif


                    <form wire:submit="update">


                        {{-- Account Code --}}

                        <div class="mb-3">

                            <label class="form-label">

                                Account Code

                                <span class="text-danger">*</span>

                            </label>

                            <input type="text"
                                class="form-control
                                    @error('code')
                                        is-invalid
                                    @enderror"
                                placeholder="Enter account code" wire:model.blur="code">

                            @error('code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Account Name --}}

                        <div class="mb-3">

                            <label class="form-label">

                                Account Name

                                <span class="text-danger">*</span>

                            </label>

                            <input type="text"
                                class="form-control
                                    @error('name')
                                        is-invalid
                                    @enderror"
                                placeholder="Enter account name" wire:model.blur="name">

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Account Type --}}

                        <div class="mb-3">

                            <label class="form-label">

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


                        {{-- Parent Account --}}

                        <div class="mb-3">

                            <label class="form-label">
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
                                Only non-postable group accounts can be parents.
                            </small>

                        </div>


                        {{-- Normal Balance --}}

                        <div class="mb-3">

                            <label class="form-label">
                                Normal Balance
                            </label>

                            <input type="text" class="form-control"
                                value="{{ $normal_balance ? ucfirst($normal_balance) : '' }}"
                                readonly>

                            <small class="text-muted">

                                Asset and Expense normally use Debit.

                                Liability, Equity and Income normally use Credit.

                            </small>

                        </div>


                        {{-- Posting Account --}}

                        <div class="mb-3">

                            <label class="form-label">
                                Posting Account
                            </label>

                            <select
                                class="form-select
                                    @error('is_postable')
                                        is-invalid
                                    @enderror"
                                wire:model="is_postable">

                                <option value="1">
                                    Yes - Posting Account
                                </option>

                                <option value="0">
                                    No - Group Account
                                </option>

                            </select>

                            @error('is_postable')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">

                                Posting accounts receive journal entries.

                                Group accounts are only used to organize
                                the Chart of Accounts.

                            </small>

                        </div>


                        {{-- Status --}}

                        <div class="mb-4">

                            <label class="form-label">
                                Status
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


                        {{-- Update Button --}}

                        <div class="d-flex justify-content-end">

                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                wire:target="update" @disabled(empty($code) || empty($name) || empty($type))>

                                <span wire:loading.remove wire:target="update">

                                    <i class="bi bi-check-lg me-1"></i>

                                    Update Account

                                </span>

                                <span wire:loading wire:target="update">

                                    <span class="spinner-border spinner-border-sm me-1"></span>

                                    Updating...

                                </span>

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>
