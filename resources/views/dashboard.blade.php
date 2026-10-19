@extends('layouts.main')
@section('title', 'Dashboard - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Welcome, {{ session('user_name') }} 👋</h4>
        <p class="text-muted mb-0">Overview of system transaction activities &mdash; <span class="badge bg-primary">{{ $role }}</span></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(in_array($role, ['SuperAdmin', 'Sales']))
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm" id="btn-create_sales">
                <i class="bi bi-cart-plus me-1"></i> New Sale
            </a>
        @endif
        @if(in_array($role, ['SuperAdmin', 'Purchase']))
            <a href="{{ route('purchases.create') }}" class="btn btn-success btn-sm" id="btn-create_purchase">
                <i class="bi bi-bag-plus me-1"></i> New Purchase
            </a>
        @endif
        @if($role === 'SuperAdmin')
            <a href="{{ route('inventories.index') }}" class="btn btn-secondary btn-sm" id="btn-manage_inventory">
                <i class="bi bi-boxes me-1"></i> Inventories
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-blue"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="stat-label">Total Items</div>
                <div class="stat-value">{{ number_format($total_inventory) }}</div>
                <div class="stat-sub">{{ number_format($total_stock) }} units in stock</div>
            </div>
        </div>
    </div>

    @if(in_array($role, ['SuperAdmin', 'Manager', 'Sales']))
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-teal"><i class="bi bi-cart-check"></i></div>
            <div>
                <div class="stat-label">{{ $role === 'Sales' ? 'My Sales' : 'Total Sales' }}</div>
                <div class="stat-value">{{ number_format($total_sales_count) }}</div>
                <div class="stat-sub">Rp {{ number_format($total_sales_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @endif

    @if(in_array($role, ['SuperAdmin', 'Manager', 'Purchase']))
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-green"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="stat-label">{{ $role === 'Purchase' ? 'My Purchases' : 'Total Purchases' }}</div>
                <div class="stat-value">{{ number_format($total_purchases_count) }}</div>
                <div class="stat-sub">Rp {{ number_format($total_purchases_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-amber"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="stat-label">Active Role</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $role }}</div>
                <div class="stat-sub text-success"><i class="bi bi-check-circle-fill me-1"></i>Active Session</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @if(in_array($role, ['SuperAdmin', 'Manager', 'Sales']))
    <div class="{{ in_array($role, ['SuperAdmin', 'Manager']) ? 'col-lg-6' : 'col-12' }}">
        <div class="app-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart-check me-2 text-info"></i><strong>Recent Sales</strong></span>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-light border" style="font-size: .8rem;">View All</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0" id="table-recent_sales">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>User</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_sales as $sale_item)
                            @php
                                $sale_total = $sale_item->details->sum(function($detail) {
                                    return (int) $detail->qty * (float) $detail->price;
                                });
                                $sale_user = $sale_item->getUser();
                            @endphp
                            <tr>
                                <td><span class="badge bg-secondary">{{ $sale_item->number }}</span></td>
                                <td>{{ date('d/m/Y', strtotime($sale_item->date)) }}</td>
                                <td>{{ $sale_user ? $sale_user->name : '-' }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($sale_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted small">No sales transactions found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(in_array($role, ['SuperAdmin', 'Manager', 'Purchase']))
    <div class="{{ in_array($role, ['SuperAdmin', 'Manager']) ? 'col-lg-6' : 'col-12' }}">
        <div class="app-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bag-plus me-2 text-success"></i><strong>Recent Purchases</strong></span>
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-light border" style="font-size: .8rem;">View All</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0" id="table-recent_purchases">
                    <thead>
                        <tr>
                            <th>PO #</th>
                            <th>Date</th>
                            <th>User</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_purchases as $purchase_item)
                            @php
                                $purchase_total = $purchase_item->details->sum(function($detail) {
                                    return (int) $detail->qty * (float) $detail->price;
                                });
                                $purchase_user = $purchase_item->getUser();
                            @endphp
                            <tr>
                                <td><span class="badge bg-secondary">{{ $purchase_item->number }}</span></td>
                                <td>{{ date('d/m/Y', strtotime($purchase_item->date)) }}</td>
                                <td>{{ $purchase_user ? $purchase_user->name : '-' }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($purchase_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted small">No purchase transactions found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
