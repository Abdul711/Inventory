<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\StockMovement;
use App\Models\Stock;
use Livewire\WithFileUploads;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use Illuminate\Validation\ValidationException;
use App\Models\ExpenseCategory;
new class extends Component {
    use WithFileUploads;

    public $supplier_id = '';
    public $category_id = '';
    public $brand_id = '';
    public $warehouse_id = '';

    public $name = '';
    public $sku = '';

    public $purchase_price = '';
    public $selling_price = '';

    public $quantity = 0;
    public $minimum_stock = 5;

    public $description = '';
    public $image;

    public $status = 1;
    public $purchase_no;
    public $purchase_date;

    public $total_amount;
    public $selling_price_after_discount;
    public $discount = 0;
    public function generateInvoiceNo()
    {
        $lastPurchase = Purchase::latest('id')->first();
        $nextId = $lastPurchase ? $lastPurchase->id + 1 : 1;

        return 'PUR-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
        $this->purchase_no = $this->generateInvoiceNo();
    }
    public function calculateDiscountPrice()
    {
        $sellingPrice = (float) $this->selling_price;
        $discount = (float) $this->discount;

        $discountAmount = ($discount / 100) * $sellingPrice;

        $this->selling_price_after_discount = ceil($sellingPrice - $discountAmount);
    }
    public function updated($property)
    {
        if (str_contains($property, 'name')) {
            $this->sku = Str::slug($this->name);
        }
        if (in_array($property, ['selling_price', 'discount'])) {
            $this->calculateDiscountPrice();
        }
    }
    public function save()
    {
        $validated = $this->validate(
            [
                'supplier_id' => 'required|exists:suppliers,id',
                'category_id' => 'required|exists:categories,id',
                'warehouse_id' => 'required',
                'brand_id' => 'required',
                'name' => 'required|min:2|max:255',
                'sku' => 'required|unique:products,sku',
                'purchase_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'minimum_stock' => 'required|integer|min:0',
                'image' => 'required|image|max:2048',
                'status' => 'required|boolean',
                'discount' => 'nullable|numeric|min:0|max:25',
            ],
            [
                'discount.max' => 'Discount cannot be greater than 25%.',
            ],
        );

        $imagePath = null;

        if ($this->image) {
            $fileName = Str::slug($this->name) . '-' . time() . '.' . $this->image->getClientOriginalExtension();

            $imagePath = $this->image->storeAs('products', $fileName, 'public');
        }
        DB::transaction(function () use ($imagePath) {
            if ($this->quantity < $this->minimum_stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Quantity cannot be less than minimum stock ({$this->minimum_stock}).",
                ]);
            }
            if ($this->purchase_price > $this->selling_price) {
                throw ValidationException::withMessages([
                    'purchase_price' => "Purchase price cannot be greater than selling price ({$this->selling_price}).",
                ]);
            }
            if ($this->selling_price_after_discount < $this->purchase_price) {
                throw ValidationException::withMessages([
                    'discount' => 'Please low the discount percentage to get the profit in product .',
                ]);
            }

            $product = Product::create([
                'supplier_id' => $this->supplier_id,
                'category_id' => $this->category_id,
                'name' => $this->name,
                'sku' => $this->sku,
                'purchase_price' => $this->purchase_price,
                'selling_price' => $this->selling_price,
                'quantity' => $this->quantity,
                'minimum_stock' => $this->minimum_stock,
                'description' => $this->description,
                'image' => $imagePath,
                'brand_id' => $this->brand_id,
                'status' => $this->status,
                'discount' => $this->discount,
                'warehouse_id' => $this->warehouse_id,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse_id,
                'supplier_id' => $this->supplier_id,
                'type' => 'purchase',
                'quantity' => $this->quantity,
                'stock_before' => 0,
                'stock_after' => $this->quantity,
                'reference_no' => $this->purchase_no,
            ]);

            Stock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $this->warehouse_id,
                ],
                [
                    'quantity' => $this->quantity,
                    'minimum_stock' => $this->minimum_stock,
                ],
            );

            $this->total_amount = (float) $this->quantity * (float) $this->purchase_price;

            $purchase = Purchase::create([
                'supplier_id' => $this->supplier_id,

                'purchase_no' => $this->purchase_no,
                'total_amount' => $this->total_amount,
                'paid_amount' => $this->total_amount,
                'due_amount' => 0,
                'payment_status' => 'paid',
                'status' => 'completed',
                'purchase_date' => date('Y-m-d'),
                'notes' => 'Item Purchased',
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $this->quantity,
                'purchase_price' => $this->purchase_price,
                'subtotal' => $this->purchase_price * $this->quantity,
            ]);

            SupplierPayment::create([
                'amount' => $this->total_amount,
                'payment_method' => 'card',
                'payment_date' => now()->format('Y-m-d'),
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'purchase_id' => $purchase->id,
                'supplier_id' => $this->supplier_id,
                'notes' => 'Item Purchased Quantity: ' . $this->quantity,
            ]);
        });
        session()->flash('success', 'Product created successfully.');

        $this->reset();

        $this->quantity = 0;
        $this->minimum_stock = 5;
        $this->status = 1;
    }

    public function suppliers()
    {
        $loginedUserRole = auth()->user()->role->name;
        if ($loginedUserRole == 'Supplier') {
            $supplier_id = auth()->user()->supplier->id;
            return Supplier::where('id', $supplier_id)->get();
        }
        return Supplier::get();
    }

    public function categories()
    {
        return Category::orderBy('name')->get();
    }
    public function brands()
    {
        return Brand::orderBy('title')->get();
    }
    public function warehouses()
    {
        return Warehouse::orderBy('name')->get();
    }
};

?>

<div class="row">

    <div class="col-lg-12">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Add Product
                </h4>

            </div>

            <div class="card-body">

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit="save">

                    <div class="row">

                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Supplier
                            </label>

                            <select class="form-select @error('supplier_id') is-invalid @enderror"
                                wire:model="supplier_id">

                                <option value="">
                                    Select Supplier
                                </option>

                                @foreach ($this->suppliers() as $supplier)
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

                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Category
                            </label>

                            <select class="form-select @error('category_id') is-invalid @enderror"
                                wire:model="category_id">

                                <option value="">
                                    Select Category
                                </option>

                                @foreach ($this->categories() as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('category_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Brand
                            </label>

                            <select class="form-select @error('brand_id') is-invalid @enderror" wire:model="brand_id">

                                <option value="">
                                    Select Brand
                                </option>

                                @foreach ($this->brands() as $brand)
                                    <option value="{{ $brand->id }}">
                                        {{ $brand->title }}
                                    </option>
                                @endforeach

                            </select>

                            @error('category_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Warehouse
                            </label>

                            <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                wire:model="warehouse_id">

                                <option value="">
                                    Select Warehouse
                                </option>

                                @foreach ($this->warehouses() as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('warehouse_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Product Name
                            </label>

                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                wire:model.live="name" placeholder="Enter product name">

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                SKU
                            </label>

                            <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                wire:model="sku" placeholder="Enter SKU" readonly>

                            @error('sku')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Purchase Price
                            </label>

                            <input type="number" step="0.01"
                                class="form-control @error('purchase_price') is-invalid @enderror"
                                wire:model.live="purchase_price">

                            @error('purchase_price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Selling Price
                            </label>

                            <input type="number" step="0.01"
                                class="form-control @error('selling_price') is-invalid @enderror"
                                wire:model.live="selling_price">

                            @error('selling_price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Discount
                            </label>

                            <input type="number" step="0.01"
                                class="form-control @error('discount') is-invalid @enderror" wire:model.live="discount">

                            @error('discount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Selling Price After Discount
                            </label>

                            <input type="number" step="0.01"
                                class="form-control @error('selling_price_after_discount') is-invalid @enderror"
                                wire:model.live="selling_price_after_discount" readonly>

                            @error('selling_price_after_discount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Quantity
                            </label>

                            <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                wire:model.live="quantity">

                            @error('quantity')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Minimum Stock
                            </label>

                            <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror"
                                wire:model.live="minimum_stock">

                            @error('minimum_stock')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea rows="4" class="form-control @error('description') is-invalid @enderror" wire:model.live="description"></textarea>

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Product Image
                            </label>

                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                wire:model="image">

                            @error('image')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div wire:loading wire:target="image" class="mt-2 text-primary">
                                Uploading image...
                            </div>

                            @if ($image)
                                <div class="mt-3">
                                    <img src="{{ $image->temporaryUrl() }}" width="150" class="img-thumbnail">
                                </div>
                            @endif

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select class="form-select" wire:model="status">

                                <option value="1">
                                    Active
                                </option>

                                <option value="0">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="save,image">

                            <span wire:loading.remove wire:target="save">
                                Save Product
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
