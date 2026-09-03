<?php

use Livewire\Component;
use App\Models\Product;
new class extends Component {
    public int $id;

    public array $product = [];

    public $reviews;

    public $salesHistory;

    public $purchaseHistory;

    public $stockMovements;
    public $saleReturnHistory;
    public $purchaseReturnHistory;
    public function mount($id): void
    {
        $this->id = (int) $id;
        $productData = Product::with(['reviews', 'purchasesitem', 'salesitem'])->find($this->id);
        /*   dd($productData->purchasereturnitem->first()->purchaseReturn->return_no);*/
        $this->product = [
            'id' => $this->id,
            'name' => $productData->name,
            'sku' => $productData->sku,
            'category' => $productData->category->name,
            'brand' => $productData->brand->title,
            'supplier' => $productData->supplier->user->name,
            'warehouse' => $productData->warehouse->name,
            'purchase_price' => $productData->purchase_price,
            'selling_price' => $productData->selling_price,
            'stock' => $productData->quantity,
            'minimum_stock' => $productData->minimum_stock,
            'status' => $productData->status,
            'description' => $productData->description,
            'profitperunit' => $productData->profit,
            'image' => asset('storage/' . $productData->image),
            'discount_amount' => $productData->discount_amount,
            'price_after_discount' => $productData->price_after_discount,
        ];

        $this->reviews = $productData->reviews;
        // $productData
        //     ->salesitem
        //     ->first()
        //     ->sale
        //     ->orderNumber
        //     ->shipment
        //     ->deliveryassign
        //     ->deliveryboy
        //     ->user
        //     ->name;

        //
        // Or the original line:
        //
        // dd($productData->salesitem->first()->sale->orderNumber->id);

        $this->salesHistory = $productData->salesitem;
        $this->saleReturnHistory = $productData->salesreturnitem;
        $this->purchaseHistory = $productData->purchasesitem;
        $this->purchaseReturnHistory = $productData->purchasereturnitem;
        $this->stockMovements = $productData->stockmovement;
    }

    public function getAverageRatingProperty(): float
    {
        if (count($this->reviews) === 0) {
            return 0;
        }

        return round(collect($this->reviews)->avg('rating'), 1);
    }

    public function getTotalSoldProperty(): int
    {
        return collect($this->salesHistory)->sum('quantity') - collect($this->saleReturnHistory)->sum('quantity');
    }

    public function getTotalPurchasedProperty(): int
    {
        return collect($this->purchaseHistory)->sum('quantity');
    }

    public function getTotalRevenueProperty(): float
    {
        return collect($this->salesHistory)->sum('total_price') - collect($this->saleReturnHistory)->sum('total_price');
    }

    public function getTotalPurchaseCostProperty(): float
    {
        return collect($this->purchaseHistory)->sum('total');
    }
};

?>

<div>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Product Details
            </h3>

            <p class="text-muted mb-0">
                Product information, inventory, reviews, sales and purchase history
            </p>
        </div>

        <a href="{{ route('products.all') }}" class="btn btn-secondary rounded-pill">
            Back
        </a>

    </div>

    <div class="row mb-4">

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Sold</h6>
                    <h3 class="text-primary fw-bold">
                        {{ $this->totalSold }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Purchased</h6>
                    <h3 class="text-success fw-bold">
                        {{ $this->totalPurchased }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h3 class="text-info fw-bold">
                        {{ number_format($this->totalRevenue, 2) }} Rs
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted">Average Rating</h6>
                    <h3 class="text-warning fw-bold">
                        {{ $this->averageRating }} ★
                    </h3>
                </div>
            </div>
        </div>

    </div>

    @if ($product['stock'] <= $product['minimum_stock'])
        <div class="alert alert-warning rounded-4">
            <strong>Low Stock Alert:</strong>
            This product stock is low. Current stock is {{ $product['stock'] }} and minimum stock is
            {{ $product['minimum_stock'] }}.
        </div>
    @endif

    <div class="card border-0 shadow mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 text-center mb-3">

                    <img src="{{ $product['image'] }}" class="img-fluid rounded shadow-sm" alt="{{ $product['name'] }}">

                </div>

                <div class="col-md-8">

                    <h2 class="fw-bold">
                        {{ $product['name'] }}
                    </h2>

                    <hr>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <strong>Product ID:</strong><br>
                            #{{ $product['id'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>SKU:</strong><br>
                            {{ $product['sku'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Category:</strong><br>
                            {{ $product['category'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Brand:</strong><br>
                            {{ $product['brand'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Supplier:</strong><br>
                            {{ $product['supplier'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Warehouse:</strong><br>
                            {{ $product['warehouse'] }}
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>Purchase Price:</strong><br>
                            {{ number_format($product['purchase_price'], 2) }}
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>Selling Price:</strong><br>
                            {{ number_format($product['selling_price'], 2) }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Selling Price After Discount:</strong><br>
                            {{ number_format($product['price_after_discount'], 2) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Profit Per Unit</strong>
                            <p>{{ $product['profitperunit'] }}</p>
                        </div>


                        <div class="col-md-6 mb-3">
                            <strong>Discount:</strong><br>
                            {{ $product['discount_amount'] }}
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>

                            @if ($product['status'])
                                <span class="badge bg-success">
                                    Active
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Inactive
                                </span>
                            @endif
                        </div>



                    </div>

                    <hr>

                    <h5>Description</h5>

                    <p class="text-muted">
                        {{ $product['description'] }}
                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Inventory Information
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <strong>Current Stock</strong>
                    <p>{{ $product['stock'] }}</p>
                </div>

                <div class="col-md-4">
                    <strong>Minimum Stock</strong>
                    <p>{{ $product['minimum_stock'] }}</p>
                </div>

                <div class="col-md-4">
                    <strong>Warehouse</strong>
                    <p>{{ $product['warehouse'] }}</p>
                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Customer Reviews
            </h5>
        </div>

        <div class="card-body">

            @forelse ($reviews as $review)

                <div class="border-bottom pb-3 mb-3">

                    <div class="d-flex justify-content-between flex-wrap gap-2">

                        <div>
                            <h6 class="fw-bold mb-1">
                                {{ $review->customer->user->name }}
                            </h6>

                            <small class="text-muted">
                                {{ $review->created_at?->format('d M Y') }}
                            </small>
                        </div>

                        <div class="text-warning">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $review->rating)
                                    <i class="bi bi-star-fill"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </div>

                    </div>

                    <p class="text-muted mt-2 mb-0">
                        {{ $review->review }}
                    </p>

                </div>

            @empty

                <p class="text-muted mb-0">
                    No reviews found.
                </p>

            @endforelse

        </div>

    </div>

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Sales History
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($salesHistory as $sale)
                            <tr>
                                <td>{{ $sale->sale->orderNumber->id }}</td>
                                <td>{{ $sale->sale->customer->user->name }}</td>
                                <td>{{ $sale['quantity'] }}</td>
                                <td>{{ number_format($sale['unit_price'], 2) }}</td>
                                <td>{{ number_format($sale['total_price'], 2) }}</td>
                                <td>{{ date('d-F-Y', strtotime($sale->sale->orderNumber->created_at)) }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No sales history found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Purchase History
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Purchase No</th>
                            <th>Supplier</th>
                            <th>Qty</th>
                            <th>Purchase Price</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($purchaseHistory as $purchase)
                            <tr>
                                <td>{{ $purchase->purchase->purchase_no }}</td>
                                <td>{{ $purchase->product->supplier->user->name }}</td>
                                <td>{{ $purchase['quantity'] }}</td>
                                <td>{{ number_format($purchase['purchase_price'], 2) }}</td>
                                <td>{{ number_format($purchase['subtotal'], 2) }}</td>
                                <td>{{ date('d-F-Y', strtotime($purchase->purchase->created_at)) }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No purchase history found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Sale Return History
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Sale Return No</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Sale Price</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($saleReturnHistory as $salereturn)
                            <tr>
                                <td>{{ $salereturn->SalesReturn->return_no }}</td>
                                <td>{{ $salereturn->SalesReturn->sale->customer->user->name }}</td>
                                <td>{{ $salereturn['quantity'] }}</td>
                                <td>{{ number_format($salereturn['unit_price'], 2) }}</td>
                                <td>{{ number_format($salereturn['total_price'], 2) }}</td>
                                <td>{{ date('d-F-Y', strtotime($salereturn->SalesReturn->created_at)) }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No sale return history found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Purchase Return History
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Sale Return No</th>

                            <th>Qty</th>
                            <th>Purchase Price</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($purchaseReturnHistory as $salereturn)
                            <tr>
                                <td>{{ $salereturn->purchaseReturn->return_no }}</td>

                                <td>{{ $salereturn['quantity'] }}</td>
                                <td>{{ number_format($salereturn['unit_price'], 2) }}</td>
                                <td>{{ number_format($salereturn['amount'], 2) }}</td>
                                <td>{{ date('d-F-Y', strtotime($salereturn->purchaseReturn->created_at)) }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No sale return history found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <div class="card border-0 shadow mb-4">

        <div class="card-header bg-light">
            <h5 class="mb-0">
                Stock Movements
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($stockMovements as $movement)
                            <tr>
                                <td>{{ date('d-F-Y', strtotime($movement['created_at'])) }}</td>

                                <td>
                                    @if ($movement['type'] === 'purchase')
                                        <span class="badge bg-success">
                                            Purchase / IN
                                        </span>
                                    @elseif ($movement['type'] === 'sale')
                                        <span class="badge bg-danger">
                                            Sale / OUT
                                        </span>
                                    @elseif ($movement['type'] === 'return')
                                        <span class="badge bg-warning">
                                            Sale Return / IN
                                        </span>
                                    @elseif($movement['type'] === 'purchase_return')
                                        <span class="badge bg-danger">
                                            Purchase Return / OUT
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($movement['type']) }}
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $movement['quantity'] }}</td>
                                <td>{{ $movement['stock_before'] }}</td>
                                <td>{{ $movement['stock_after'] }}</td>
                                <td>{{ $movement['remarks'] }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No stock movements found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow">

        <div class="card-body">

            <div class="d-flex gap-2 flex-wrap">

                <a href="{{ route('products.edit', $product['id']) }}" class="btn btn-primary rounded-pill">
                    Edit Product
                </a>

                <a href="#" class="btn btn-success rounded-pill">
                    Add Stock
                </a>

                <a href="{{ route('purchases.create') }}" class="btn btn-info rounded-pill text-white">
                    Create Purchase
                </a>

                <a href="#" class="btn btn-warning rounded-pill">
                    View Sales
                </a>

            </div>

        </div>

    </div>

</div>
