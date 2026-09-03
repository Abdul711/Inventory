<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component {
    public string $search = '';

    public function with(): array
    {
        return [
            'accounts' => Account::query()
                ->whereNull('parent_id')
                ->with([
                    'children' => fn($query) => $query->orderBy('code'),
                    'children.children' => fn($query) => $query->orderBy('code'),
                ])
                ->orderBy('code')
                ->get(),
        ];
    }
};

?>

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Chart of Accounts</h3>
            <p class="text-muted mb-0">
                Manage your accounting accounts
            </p>
        </div>
    </div>

    {{-- Search --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <input type="text" class="form-control" placeholder="Search accounts..."
                wire:model.live.debounce.300ms="search">

        </div>
    </div>

    {{-- Accounts --}}
    @foreach ($accounts as $account)
        <div class="card border-0 shadow-sm mb-3">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between">

                    <div class="fw-bold">

                        {{ $account->code }}

                        -

                        {{ $account->name }}

                    </div>

                    <span class="badge bg-primary">
                        {{ ucfirst($account->type) }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @foreach ($account->children as $child)
                    <div class="mb-3">

                        <div class="fw-semibold">

                            {{ $child->code }}

                            -

                            {{ $child->name }}

                        </div>

                        @foreach ($child->children as $subAccount)
                            <div class="ms-4 mt-2">

                                <span class="text-muted">
                                    {{ $subAccount->code }}
                                </span>

                                -

                                {{ $subAccount->name }}

                            </div>
                        @endforeach

                    </div>
                @endforeach

            </div>

        </div>
    @endforeach

</div>
