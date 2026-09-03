<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
new class extends Component {
    public $supplier_id = '';

    public $purchase_id = '';

    public $payment_date = '';

    public $payment_method = 'cash';

    public $transaction_id = '';

    public $amount = '';

    public $notes = '';

    public $suppliers = [];

    public $purchases = [];

    public $purchase = null;

    public $total_amount = 0;

    public $paid_amount = 0;

    public $due_amount = 0;

    public function mount()
    {
        $this->payment_date = now()->format('Y-m-d');

        $this->suppliers = Supplier::where('status', 1)->get();
    }

    protected function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',

            'purchase_id' => 'required|exists:purchases,id',

            'payment_date' => 'required|date',

            'payment_method' => 'required',

            'amount' => 'required|numeric|min:1',

            'transaction_id' => 'nullable|max:255',

            'notes' => 'nullable|max:1000',
        ];
    }

    protected $messages = [
        'supplier_id.required' => 'Please select supplier.',

        'purchase_id.required' => 'Please select purchase invoice.',

        'amount.required' => 'Payment amount is required.',

        'amount.numeric' => 'Invalid payment amount.',

        'payment_method.required' => 'Please select payment method.',
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    /*
    |--------------------------------------------------------------------------
    | Supplier Changed
    |--------------------------------------------------------------------------
    */

    public function updatedSupplierId()
    {
        $this->purchase_id = '';

        $this->purchase = null;

        $this->amount = '';

        $this->total_amount = 0;

        $this->paid_amount = 0;

        $this->due_amount = 0;

        $this->purchases = Purchase::where('supplier_id', $this->supplier_id)

            ->where('due_amount', '>', 0)

            ->latest()

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase Changed
    |--------------------------------------------------------------------------
    */

    public function updatedPurchaseId()
    {
        if (!$this->purchase_id) {
            return;
        }

        $purchase = Purchase::find($this->purchase_id);

        if (!$purchase) {
            return;
        }

        $this->purchase = $purchase;

        $this->total_amount = $purchase->total_amount;

        $this->paid_amount = $purchase->paid_amount;

        $this->due_amount = $purchase->due_amount;

        /*
        Default payment is remaining due.
        */

        $this->amount = $purchase->due_amount;
    }

    /*
    |--------------------------------------------------------------------------
    | Save Payment
    |--------------------------------------------------------------------------
    */

    public function save()
    {
        $validated = $this->validate();

        $purchase = Purchase::findOrFail($this->purchase_id);

        if ($this->amount <= 0) {
            $this->addError('amount', 'Payment amount must be greater than zero.');

            return;
        }

        if ($this->amount > $purchase->due_amount) {
            $this->addError('amount', 'Payment cannot exceed due amount.');

            return;
        }

        DB::transaction(function () use ($purchase) {
            /*
            |--------------------------------------------------------------------------
            | Create Supplier Payment
            |--------------------------------------------------------------------------
            */

            $payment = SupplierPayment::create([
                'supplier_id' => $purchase->supplier_id,

                'purchase_id' => $purchase->id,

                'payment_date' => $this->payment_date,

                'payment_method' => $this->payment_method,

                'transaction_id' => $this->transaction_id,

                'amount' => $this->amount,

                'notes' => $this->notes,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Purchase
            |--------------------------------------------------------------------------
            */

            $purchase->paid_amount += $this->amount;

            $purchase->due_amount -= $this->amount;

            if ($purchase->due_amount <= 0) {
                $purchase->due_amount = 0;

                $purchase->payment_status = 'paid';
            } elseif ($purchase->paid_amount > 0) {
                $purchase->payment_status = 'partial';
            } else {
                $purchase->payment_status = 'pending';
            }

            $purchase->save();

            /*
            |--------------------------------------------------------------------------
            | Expense Entry
            |--------------------------------------------------------------------------
            */
        });

        /*
        |--------------------------------------------------------------------------
        | Reset Form
        |--------------------------------------------------------------------------
        */

        $this->reset(['purchase_id', 'purchase', 'amount', 'notes', 'transaction_id', 'total_amount', 'paid_amount', 'due_amount', 'purchases']);

        $this->supplier_id = '';

        $this->payment_method = 'cash';

        $this->payment_date = now()->format('Y-m-d');

        session()->flash(
            'success',

            'Supplier payment added successfully.',
        );
    }
};
?>
<div class="row">

    <div class="col-lg-12">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Add Supplier Payment
                </h4>

            </div>

            <div class="card-body">

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">

                        <strong>Please fix the following errors:</strong>

                        <ul class="mb-0 mt-2">

                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>
                @endif

                <form wire:submit="save">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Supplier
                            </label>

                            <select class="form-select @error('supplier_id') is-invalid @enderror"
                                wire:model.live="supplier_id">

                                <option value="">
                                    Select Supplier
                                </option>

                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">

                                        {{ $supplier->user->name }}

                                    </option>
                                @endforeach

                            </select>

                            @error('supplier_id')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Purchase Invoice

                            </label>

                            <select class="form-select @error('purchase_id') is-invalid @enderror"
                                wire:model.live="purchase_id" @disabled(empty($supplier_id))>

                                <option value="">

                                    Select Purchase

                                </option>

                                @foreach ($purchases as $purchase)
                                    <option value="{{ $purchase->id }}">

                                        {{ $purchase->purchase_no }}

                                        (Due:

                                        Rs {{ number_format($purchase->due_amount, 2) }})
                                    </option>
                                @endforeach

                            </select>

                            @error('purchase_id')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                    </div>

                    @if ($purchase)
                        <div class="card border bg-light mb-4">

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-3">

                                        <strong>Total Purchase</strong>

                                        <h5 class="mt-2">

                                            Rs {{ number_format($total_amount, 2) }}

                                        </h5>

                                    </div>

                                    <div class="col-md-3">

                                        <strong>Already Paid</strong>

                                        <h5 class="text-success mt-2">

                                            Rs {{ number_format($paid_amount, 2) }}

                                        </h5>

                                    </div>

                                    <div class="col-md-3">

                                        <strong>Remaining Due</strong>

                                        <h5 class="text-danger mt-2">

                                            Rs {{ number_format($due_amount, 2) }}

                                        </h5>

                                    </div>

                                    <div class="col-md-3">

                                        <strong>Status</strong>

                                        <div class="mt-2">

                                            <span
                                                class="badge
                                        @if ($purchase->payment_status == 'paid') bg-success
                                        @elseif($purchase->payment_status == 'partial')
                                            bg-warning
                                        @else
                                            bg-danger @endif">

                                                {{ ucfirst($purchase->payment_status) }}

                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                    @endif

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">

                                Payment Date

                            </label>

                            <input type="date" wire:model.live="payment_date"
                                class="form-control @error('payment_date') is-invalid @enderror">

                            @error('payment_date')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">

                                Payment Method

                            </label>

                            <select wire:model.live="payment_method"
                                class="form-select @error('payment_method') is-invalid @enderror">

                                <option value="cash">

                                    Cash

                                </option>

                                <option value="bank">

                                    Bank

                                </option>

                                <option value="card">

                                    Card

                                </option>

                                <option value="cheque">

                                    Cheque

                                </option>

                            </select>

                            @error('payment_method')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">

                                Transaction ID

                            </label>

                            <input type="text" wire:model.live="transaction_id"
                                class="form-control @error('transaction_id') is-invalid @enderror"
                                placeholder="Optional">

                            @error('transaction_id')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Payment Amount

                            </label>

                            <input type="number" step="0.01" wire:model.live="amount"
                                class="form-control @error('amount') is-invalid @enderror">

                            @error('amount')
                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Remaining After Payment

                            </label>

                            <input type="text" class="form-control" readonly
                                value="{{ max(0, (float) $due_amount - (float) $amount) }}">

                        </div>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">

                            Notes

                        </label>

                        <textarea rows="4" wire:model.live="notes" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional Notes"></textarea>

                        @error('notes')
                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>
                        @enderror

                    </div>

                    <div class="d-flex justify-content-between">

                        <a wire:navigate href="{{ route('suppliers.payment.index') }}" class="btn btn-secondary">

                            Back

                        </a>

                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="save">

                            <span wire:loading.remove wire:target="save">

                                Save Payment

                            </span>

                            <span wire:loading wire:target="save">

                                Saving...

                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
