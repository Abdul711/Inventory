<?php

use Livewire\Component;
use App\Models\Product;
new class extends Component {
    public $products;
    public function mount()
    {
        $loginedUserRole = auth()->user()->role->name;
        if ($loginedUserRole != 'Supplier') {
            $this->products = Product::with(['category', 'brand', 'supplier', 'warehouse'])->get();
        } else {
            $supplier_id = auth()->user()->supplier->id;
            $this->products = Product::with(['category', 'brand', 'supplier', 'warehouse'])
                ->where('supplier_id', $supplier_id)
                ->get();
        }
    }
};
?>

<div>
    {{-- You must be the change you wish to see in the world. - Mahatma Gandhi --}}


    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Product List
            </h3>

            <p class="text-muted mb-0">
                Manage all products
            </p>

        </div>



    </div>

    <!-- Product Table -->

    <div class="dashboard-card">

        <!-- Search -->

        <div class="row mb-4">

            <div class="col-md-6">

                <input type="text" class="form-control rounded-4" placeholder="Search product...">

            </div>

        </div>

        <!-- Table -->

        <div class="table-responsive">

            <table class="table align-middle">
                <a href="{{ route('products.report') }}">Report</a>
                <thead class="table-light">

                    <tr>

                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    <!-- Product 1 -->
                    @foreach ($products as $product)
                        <tr>

                            <td>#1001</td>

                            <td>

                                <img src="{{ asset('storage/' . $product->image) }}" width="50" class="rounded-3">

                            </td>

                            <td>

                                <div class="fw-semibold">
                                    {{ ucfirst($product->name) }}
                                </div>

                            </td>

                            <td>{{ $product->category->name }}</td>

                            <td>{{ $product->selling_price }}</td>

                            <td>{{ $product->quantity }}</td>

                            <td>

                                <span class="badge bg-success rounded-pill">
                                    In Stock
                                </span>

                            </td>

                            <td>

                                <a href="{{ route('products.edit', $product->id) }}"
                                    class="btn btn-sm btn-primary rounded-pill">
                                    Edit
                                </a>
                                <a href="{{ route('products.show', $product->id) }}"
                                    class="btn btn-sm btn-info rounded-pill">
                                    View
                                </a>
                                <button class="btn btn-sm btn-danger rounded-pill">
                                    Delete
                                </button>

                            </td>

                        </tr>
                    @endforeach

                    <!-- Product 2 -->



                    <!-- Product 3 -->



                    <!-- Product 4 -->



                    <!-- Product 5 -->



                </tbody>

            </table>

        </div>

    </div>

</div>
