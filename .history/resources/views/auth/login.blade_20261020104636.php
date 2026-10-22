@extends('layouts.main')
@section('title', 'Login - Sales & Purchases Application')

@section('content')
<div class="login-wrapper py-5" style="min-height: calc(100vh - 160px);">
    <div class="login-card mx-auto" style="max-width: 440px;">


        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1" style="color: #2d3748;">Welcome Back</h4>
            <p class="text-muted" style="font-size: .88rem;">Select your user account to access the system</p>
        </div>

        <form id="form-login" action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="select-user_name" class="form-label fw-semibold">Account / Role</label>
                <select name="name" id="select-user_name" class="form-select select2-user" required>
                    <option value="">-- Select User Account --</option>
                    <option value="Super Administrator">Super Administrator (SuperAdmin)</option>
                    <option value="Pegawai Sales">Pegawai Sales (Sales)</option>
                    <option value="Pegawai Purchase">Pegawai Purchase (Purchase)</option>
                    <option value="Operational Manager">Operational Manager (Manager)</option>
                </select>
            </div>

            <button id="btn-login" type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <p class="text-muted mb-2" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;">Role Access Permissions</p>
            <div class="d-flex flex-column gap-2" style="font-size: .85rem;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill px-2">SuperAdmin</span>
                    <span class="text-muted">Full CRUD for all features</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info text-dark rounded-pill px-2">Sales</span>
                    <span class="text-muted">CRUD for own sales transactions</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-pill px-2">Purchase</span>
                    <span class="text-muted">CRUD for own purchase transactions</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark rounded-pill px-2">Manager</span>
                    <span class="text-muted">View, export &amp; print reports</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    window.routes = {
        loginProcess: "{{ Route::has('api.login') ? route('api.login') : url('/api/login') }}"
    };
</script>
<script src="{{ asset('js/modules/auth.js') }}"></script>
@endpush