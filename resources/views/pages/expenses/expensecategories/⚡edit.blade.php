<?php

use Livewire\Component;
use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use App\Models\Account;
new class extends Component {
    public $expenseCategoryId;
    public $name = '';
    public $description = '';
    public $status = 1;
    public $account_id = '';
    public array $expenseAccounts = [];
    public function mount($id): void
    {
        $category = ExpenseCategory::findOrFail($id);

        $this->expenseCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->status = $category->status;
        $this->account_id = $category->account_id;

        $this->expenseAccounts = Account::query()->where('type', 'expense')->where('is_group', false)->where('is_active', true)->orderBy('code')->get()->toArray();
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'min:2', 'max:255', Rule::unique('expense_categories', 'name')->ignore($this->expenseCategoryId)],
            'description' => 'nullable|max:1000',
            'status' => 'required|boolean',
        ];
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function update()
    {
        $this->validate();

        ExpenseCategory::findOrFail($this->expenseCategoryId)->update([
            'name' => $this->name,
            'description' => $this->description,
            'account_id' => $this->account_id,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Expense category updated successfully.');
    }
};
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow border-0">
            <div class="card-header bg-warning">
                <h4 class="mb-0">Edit Expense Category</h4>
            </div>

            <div class="card-body">

                @if (session()->has('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form wire:submit="update">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" wire:model.live="name"
                            class="form-control @error('name') is-invalid @enderror">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea wire:model.live="description" class="form-control @error('description') is-invalid @enderror" rows="4"></textarea>

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

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary rounded-pill">
                            Back
                        </a>

                        <button class="btn btn-warning rounded-pill" wire:loading.attr="disabled">
                            <span wire:loading.remove>Update Category</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
