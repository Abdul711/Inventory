<?php

use Livewire\Component;
use App\Models\Employee;
use App\Models\BankAccount;
use App\Models\EmployeeBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

new class extends Component {
    public int $id;
    public array $employee = [];
    public array $bankAccounts = [];

    public ?int $bank_account_id = null;
    public string $bank_name = '';
    public string $account_title = '';
    public string $account_number = '';
    public string $iban = '';
    public string $branch_name = '';
    public string $branch_code = '';
    public string $swift_code = '';
    public bool $is_primary = true;
    public string $notes = '';

    public function mount($id): void
    {
        $this->id = (int) $id;

        $employee = Employee::with(['user', 'department'])->findOrFail($this->id);

        $this->employee = [
            'id' => $employee->id,
            'name' => $employee->user?->name ?? 'Employee',
            'email' => $employee->user?->email ?? '—',
            'code' => $employee->employee_code,
            'department' => $employee->department?->name ?? '—',
            'designation' => $employee->designation ?? '—',
        ];

        $this->loadBankAccounts();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'bank_name' => ['required', 'string', 'max:120'],
            'account_title' => ['required', 'string', 'max:120'],
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bank_accounts', 'account_number')->ignore($this->bank_account_id),
            ],
            'iban' => ['nullable', 'string', 'max:50'],
            'branch_name' => ['nullable', 'string', 'max:120'],
            'branch_code' => ['nullable', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['is_primary']) {
                $employeeBankAccountIds = EmployeeBankAccount::where('employee_id', $this->id)
                    ->whereNotNull('bank_account_id')
                    ->pluck('bank_account_id');

                BankAccount::whereIn('id', $employeeBankAccountIds)
                    ->update(['is_primary' => false]);
            }

            $bankAccount = $this->bank_account_id
                ? BankAccount::findOrFail($this->bank_account_id)
                : new BankAccount();

            $bankAccount->fill([
                ...$validated,
                'bank_name' => trim($validated['bank_name']),
                'account_title' => trim($validated['account_title']),
                'account_number' => trim($validated['account_number']),
                'iban' => $validated['iban'] ? strtoupper(str_replace(' ', '', $validated['iban'])) : null,
                'branch_name' => $validated['branch_name'] ?: null,
                'branch_code' => $validated['branch_code'] ?: null,
                'swift_code' => $validated['swift_code'] ? strtoupper($validated['swift_code']) : null,
                'notes' => $validated['notes'] ?: null,
                'account_type' => 'employee',
            ]);
            $bankAccount->save();

            EmployeeBankAccount::firstOrCreate([
                'employee_id' => $this->id,
                'bank_account_id' => $bankAccount->id,
            ]);
        });

        session()->flash(
            'success',
            $this->bank_account_id ? 'Bank account updated successfully.' : 'Bank account added successfully.'
        );

        $this->resetForm();
        $this->loadBankAccounts();
    }

    public function edit(int $bankAccountId): void
    {
        $this->ensureEmployeeOwnsAccount($bankAccountId);

        $bankAccount = BankAccount::findOrFail($bankAccountId);

        $this->bank_account_id = $bankAccount->id;
        $this->bank_name = $bankAccount->bank_name ?? '';
        $this->account_title = $bankAccount->account_title ?? '';
        $this->account_number = $bankAccount->account_number ?? '';
        $this->iban = $bankAccount->iban ?? '';
        $this->branch_name = $bankAccount->branch_name ?? '';
        $this->branch_code = $bankAccount->branch_code ?? '';
        $this->swift_code = $bankAccount->swift_code ?? '';
        $this->is_primary = (bool) $bankAccount->is_primary;
        $this->notes = $bankAccount->notes ?? '';

        $this->resetValidation();
    }

    public function delete(int $bankAccountId): void
    {
        $this->ensureEmployeeOwnsAccount($bankAccountId);

        DB::transaction(function () use ($bankAccountId) {
            EmployeeBankAccount::where('employee_id', $this->id)
                ->where('bank_account_id', $bankAccountId)
                ->delete();

            $stillInUse = EmployeeBankAccount::where('bank_account_id', $bankAccountId)->exists();

            if (! $stillInUse) {
                BankAccount::whereKey($bankAccountId)->delete();
            }
        });

        if ($this->bank_account_id === $bankAccountId) {
            $this->resetForm();
        }

        $this->loadBankAccounts();
        session()->flash('success', 'Bank account removed successfully.');
    }

    public function resetForm(): void
    {
        $this->bank_account_id = null;
        $this->bank_name = '';
        $this->account_title = '';
        $this->account_number = '';
        $this->iban = '';
        $this->branch_name = '';
        $this->branch_code = '';
        $this->swift_code = '';
        $this->is_primary = true;
        $this->notes = '';
        $this->resetValidation();
    }

    private function loadBankAccounts(): void
    {
        $bankAccountIds = EmployeeBankAccount::where('employee_id', $this->id)
            ->whereNotNull('bank_account_id')
            ->pluck('bank_account_id');

        $this->bankAccounts = BankAccount::whereIn('id', $bankAccountIds)
            ->where('account_type', 'employee')
            ->orderByDesc('is_primary')
            ->latest()
            ->get()
            ->map(fn (BankAccount $account) => [
                'id' => $account->id,
                'bank_name' => $account->bank_name,
                'account_title' => $account->account_title,
                'account_number' => $this->maskAccountNumber($account->account_number),
                'iban' => $account->iban ?: '—',
                'branch_name' => $account->branch_name ?: '—',
                'branch_code' => $account->branch_code ?: '—',
                'swift_code' => $account->swift_code ?: '—',
                'is_primary' => (bool) $account->is_primary,
            ])
            ->values()
            ->toArray();
    }

    private function ensureEmployeeOwnsAccount(int $bankAccountId): void
    {
        EmployeeBankAccount::where('employee_id', $this->id)
            ->where('bank_account_id', $bankAccountId)
            ->firstOrFail();
    }

    private function maskAccountNumber(?string $accountNumber): string
    {
        if (! $accountNumber) {
            return '—';
        }

        $visible = substr($accountNumber, -4);
        $maskedLength = max(strlen($accountNumber) - 4, 4);

        return str_repeat('•', $maskedLength) . $visible;
    }
};
?>

<div>
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Employee Bank Account Details</h3>
            <p class="text-muted mb-0">Add or update bank information used for salary payments</p>
        </div>

        <a href="{{ route('employees.show', $employee['id']) }}" class="btn btn-secondary rounded-pill px-4">
            Back
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Employee Summary --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Employee</h6>
                    <h5 class="fw-bold mb-1">{{ $employee['name'] }}</h5>
                    <small class="text-muted">{{ $employee['code'] }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Department</h6>
                    <h5 class="fw-bold mb-1">{{ $employee['department'] }}</h5>
                    <small class="text-muted">{{ $employee['designation'] }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Saved Accounts</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ count($bankAccounts) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Account Form --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div>
                <h5 class="mb-0 fw-bold">{{ $bank_account_id ? 'Update Bank Information' : 'Bank Information' }}</h5>
                <small class="text-muted">Enter the employee's salary account details</small>
            </div>

            @if ($bank_account_id)
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Editing account</span>
            @endif
        </div>

        <div class="card-body p-4">
            <form wire:submit="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="bank_name"
                            class="form-control @error('bank_name') is-invalid @enderror"
                            placeholder="e.g. Meezan Bank">
                        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Account Title <span class="text-danger">*</span></label>
                        <input type="text" wire:model="account_title"
                            class="form-control @error('account_title') is-invalid @enderror"
                            placeholder="Account holder name">
                        @error('account_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
                        <input type="text" wire:model="account_number"
                            class="form-control @error('account_number') is-invalid @enderror"
                            placeholder="Enter account number" autocomplete="off">
                        @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">IBAN</label>
                        <input type="text" wire:model="iban"
                            class="form-control text-uppercase @error('iban') is-invalid @enderror"
                            placeholder="PK00BANK0000000000000000" autocomplete="off">
                        @error('iban') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Branch Name</label>
                        <input type="text" wire:model="branch_name"
                            class="form-control @error('branch_name') is-invalid @enderror"
                            placeholder="Branch name">
                        @error('branch_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Branch Code</label>
                        <input type="text" wire:model="branch_code"
                            class="form-control @error('branch_code') is-invalid @enderror"
                            placeholder="Branch code">
                        @error('branch_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">SWIFT / BIC Code</label>
                        <input type="text" wire:model="swift_code"
                            class="form-control text-uppercase @error('swift_code') is-invalid @enderror"
                            placeholder="Optional international bank code">
                        @error('swift_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold d-block">Salary Account</label>
                        <div class="border rounded-3 px-3 py-2 bg-light">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="is_primary" wire:model="is_primary">
                                <label class="form-check-label" for="is_primary">
                                    Set as primary salary account
                                </label>
                            </div>
                        </div>
                        @error('is_primary') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea wire:model="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional payment or bank instructions"></textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap border-top pt-3 mt-2">
                    @if ($bank_account_id)
                        <button type="button" wire:click="resetForm" class="btn btn-outline-secondary rounded-pill px-4">
                            Cancel Edit
                        </button>
                    @else
                        <button type="button" wire:click="resetForm" class="btn btn-outline-secondary rounded-pill px-4">
                            Clear
                        </button>
                    @endif

                    <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $bank_account_id ? 'Update Bank Account' : 'Save Bank Account' }}
                        </span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Existing Accounts --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold">Saved Bank Accounts</h5>
        </div>

        <div class="card-body p-0">
            @forelse ($bankAccounts as $account)
                <div class="p-4 {{ ! $loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                <h5 class="fw-bold mb-0">{{ $account['bank_name'] }}</h5>
                                @if ($account['is_primary'])
                                    <span class="badge bg-success rounded-pill">Primary</span>
                                @endif
                            </div>
                            <div class="text-muted">{{ $account['account_title'] }}</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" wire:click="edit({{ $account['id'] }})"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Edit
                            </button>
                            <button type="button"
                                wire:click="delete({{ $account['id'] }})"
                                wire:confirm="Are you sure you want to remove this bank account?"
                                class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                Remove
                            </button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Account Number</small>
                            <span class="fw-semibold">{{ $account['account_number'] }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">IBAN</small>
                            <span class="fw-semibold">{{ $account['iban'] }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Branch</small>
                            <span class="fw-semibold">{{ $account['branch_name'] }}</span>
                            @if ($account['branch_code'] !== '—')
                                <small class="text-muted">({{ $account['branch_code'] }})</small>
                            @endif
                        </div>
                        <div class="col-md-4 mb-0">
                            <small class="text-muted d-block">SWIFT / BIC</small>
                            <span class="fw-semibold">{{ $account['swift_code'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 px-3">
                    <h6 class="fw-bold mb-1">No bank account added yet</h6>
                    <p class="text-muted mb-0">Complete the form above to add the employee's salary account.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Security Note --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-start gap-3">
            <div>
                <h6 class="fw-bold mb-1">Bank information is sensitive</h6>
                <p class="text-muted mb-0">
                    Only authorized HR, payroll staff, and the relevant employee should be able to view or update these details.
                    Account numbers are masked in the saved-account summary.
                </p>
            </div>
        </div>
    </div>
</div>
