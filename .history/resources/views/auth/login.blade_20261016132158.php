@extends('layouts.app')

@section('title', 'Login - Projek Penjualan')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-4">
        <div class="card shadow-lg p-4 rounded-4">
            <div class="text-center mb-4">
                <h3 class="fw-bold">Login Aplikasi</h3>
                <p class="text-muted small">Silakan pilih akun untuk masuk</p>
            </div>

            <form id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Pilih User / Role</label>
                    <select name="name" id="name" class="form-select" required>
                        <option value="">-- Pilih User --</option>
                        <option value="Super Administrator">Super Administrator (SuperAdmin)</option>
                        <option value="Pegawai Sales">Pegawai Sales (Sales)</option>
                        <option value="Pegawai Purchase">Pegawai Purchase (Purchase)</option>
                        <option value="Operational Manager">Operational Manager (Manager)</option>
                    </select>
                </div>

                <button type="submit" id="btnLogin" class="btn btn-primary w-100 py-2 fw-semibold">Login</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            let btn = $('#btnLogin');
            btn.prop('disabled', true).text('Memproses...');

            $.ajax({
                url: "{{ route('login.process') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Login');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan atau user tidak valid.'
                    });
                }
            });
        });
    });
</script>
@endpush