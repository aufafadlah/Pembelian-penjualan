@php
    $current_role = session('user_role');
    $current_route = Route::currentRouteName();
@endphp

@if(session()->has('user_id'))
<nav class="navbar navbar-expand-lg app-navbar sticky-top" id="navbar-main">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <div class="brand-dot"><i class="bi bi-box-seam-fill"></i></div>
            <span>Sales & <span class="fw-normal text-muted">Purchases</span></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none p-1" type="button"
                data-bs-toggle="collapse" data-bs-target="#nav-main_collapse" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4 text-muted"></i>
        </button>

        <div class="collapse navbar-collapse" id="nav-main_collapse">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ $current_route === 'dashboard' ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-1x2"></i> Dashboard
                    </a>
                </li>

                @if($current_role === 'SuperAdmin')
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with($current_route, 'inventories') ? 'active' : '' }}"
                       href="{{ route('inventories.index') }}">
                        <i class="bi bi-boxes"></i> Inventories
                    </a>
                </li>
                @endif

                @if(in_array($current_role, ['SuperAdmin', 'Sales', 'Manager']))
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with($current_route, 'sales') ? 'active' : '' }}"
                       href="{{ route('sales.index') }}">
                        <i class="bi bi-cart-check"></i> Sales
                    </a>
                </li>
                @endif

                @if(in_array($current_role, ['SuperAdmin', 'Purchase', 'Manager']))
                <li class="nav-item">
                    <a class="nav-link {{ str_starts_with($current_route, 'purchases') ? 'active' : '' }}"
                       href="{{ route('purchases.index') }}">
                        <i class="bi bi-bag-plus"></i> Purchases
                    </a>
                </li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <div class="role-chip">
                    <i class="bi bi-person-circle text-muted"></i>
                    <span>{{ session('user_name') }}</span>
                    <span class="role-badge">{{ $current_role }}</span>
                </div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-light border" id="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>
@endif
