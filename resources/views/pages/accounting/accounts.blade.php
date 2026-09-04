<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component {
    public string $search = '';

    public function with(): array
    {
        $search = trim($this->search);

        $accounts = Account::query()
            ->whereNull('parent_id')

            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    // Match parent account
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")

                        // Match child account
                        ->orWhereHas('children', function ($childQuery) use ($search) {
                            $childQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                        })

                        // Match sub-account
                        ->orWhereHas('children.children', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })

            ->with([
                'children' => function ($query) {
                    $query->orderBy('code');
                },

                'children.children' => function ($query) {
                    $query->orderBy('code');
                },
            ])

            ->orderBy('code')
            ->get();

        return [
            'accounts' => $accounts,
        ];
    }
};

?>

<div>

    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="fw-bold mb-1">
                    Chart of Accounts
                </h3>

                <p class="text-muted mb-0">
                    Manage your accounting accounts
                </p>
            </div>

            <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Add Account
            </a>

        </div>


        {{-- Search --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="input-group">

                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>

                    <input type="text" class="form-control" placeholder="Search by account name or code..."
                        wire:model.live.debounce.300ms="search">

                    @if ($search)
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('search', '')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    @endif

                </div>

            </div>

        </div>


        {{-- Accounts --}}
        @forelse ($accounts as $account)

            <div class="card border-0 shadow-sm mb-3">

                {{-- Main Account --}}
                <div class="card-header bg-white py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="fw-bold fs-6">

                                {{ $account->code }}
                                -
                                {{ $account->name }}

                            </div>

                            <div class="mt-2">

                                @if ($account->is_postable)
                                    <span class="badge bg-success">
                                        Postable
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Non-Postable
                                    </span>
                                @endif

                            </div>

                        </div>


                        <div class="d-flex align-items-center gap-2">

                            @if ($account->type)
                                <span class="badge bg-primary">
                                    {{ ucfirst($account->type) }}
                                </span>
                            @endif


                            <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i>
                                Edit
                            </a>

                        </div>

                    </div>

                </div>


                {{-- Children --}}
                <div class="card-body">

                    @forelse ($account->children as $child)
                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="fw-semibold">

                                        {{ $child->code }}
                                        -
                                        {{ $child->name }}

                                    </div>

                                    <div class="mt-2">

                                        @if ($child->is_postable)
                                            <span class="badge bg-success">
                                                Postable
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                Non-Postable
                                            </span>
                                        @endif

                                    </div>

                                </div>


                                <div class="d-flex align-items-center gap-2">

                                    @if ($child->type)
                                        <span class="badge bg-light text-dark border">
                                            {{ ucfirst($child->type) }}
                                        </span>
                                    @endif


                                    <a href="{{ route('accounts.edit', $child->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>
                                        Edit
                                    </a>

                                </div>

                            </div>


                            {{-- Sub Accounts --}}
                            @if ($child->children->isNotEmpty())
                                <div class="mt-3">

                                    @foreach ($child->children as $subAccount)
                                        <div class="ms-4 mt-2 border-start border-3 ps-3 py-2">

                                            <div class="d-flex justify-content-between align-items-center">

                                                <div>

                                                    <div>

                                                        <span class="text-muted fw-semibold">
                                                            {{ $subAccount->code }}
                                                        </span>

                                                        -

                                                        <span>
                                                            {{ $subAccount->name }}
                                                        </span>

                                                    </div>


                                                    <div class="mt-2">

                                                        @if ($subAccount->is_postable)
                                                            <span class="badge bg-success">
                                                                Postable
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                Non-Postable
                                                            </span>
                                                        @endif

                                                    </div>

                                                </div>


                                                <div class="d-flex align-items-center gap-2">

                                                    @if ($subAccount->type)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ ucfirst($subAccount->type) }}
                                                        </span>
                                                    @endif


                                                    <a href="{{ route('accounts.edit', $subAccount->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil me-1"></i>
                                                        Edit
                                                    </a>

                                                </div>

                                            </div>

                                        </div>
                                    @endforeach

                                </div>
                            @endif

                        </div>

                    @empty

                        <div class="text-muted text-center py-3">
                            No child accounts available.
                        </div>
                    @endforelse

                </div>

            </div>

        @empty

            {{-- Empty State --}}
            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <i class="bi bi-journal-x fs-1 text-muted"></i>

                    <h5 class="mt-3">
                        No accounts found
                    </h5>

                    @if ($search)
                        <p class="text-muted mb-3">
                            No account matches
                            <strong>{{ $search }}</strong>.
                        </p>

                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('search', '')">
                            Clear Search
                        </button>
                    @else
                        <p class="text-muted mb-3">
                            Your Chart of Accounts is currently empty.
                        </p>

                        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Create First Account
                        </a>
                    @endif

                </div>

            </div>

        @endforelse

    </div>

</div>
