<?php

use Livewire\Component;
use App\Models\ExpenseCategory;
use App\Models\Account;
new class extends Component {
    public $name = '';
    public $description = '';
    public $status = 1;
    public $account_id = '';
    public array $expenseAccounts = [];
    public function mount(): void
    {
        $this->expenseAccounts = Account::query()->where('type', 'expense')->where('is_group', false)->where('is_active', true)->orderBy('code')->get()->toArray();
    }
    protected $rules = [
        'name' => 'required|min:2|max:255|unique:expense_categories,name',
        'description' => 'nullable|max:1000',
        'status' => 'required|boolean',
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function save()
    {
        $this->validate();

        ExpenseCategory::create([
            'name' => $this->name,
            'description' => $this->description,
            'account_id' => $this->account_id,
            'status' => $this->status,
        ]);

        $this->reset(['name', 'description']);
        $this->status = 1;

        session()->flash('success', 'Expense category added successfully.');
    }
};
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add Expense Category</h4>
            </div>

            <div class="card-body">

                @if (session()->has('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form wire:submit="save">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" wire:model.live="name"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter category name">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea wire:model.live="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                            placeholder="Enter description"></textarea>

                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select wire:model.live="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Accounting Account
                        </label>

                        <select wire:model="account_id" class="form-select">

                            <option value="">
                                Select Accounting Account
                            </option>

                            @foreach ($expenseAccounts as $account)
                                <option value="{{ $account['id'] }}">

                                    {{ $account['code'] }}
                                    -
                                    {{ $account['name'] }}

                                </option>
                            @endforeach

                        </select>

                        @error('account_id')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary rounded-pill" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Category</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
