@extends('layouts.main')
@section('title', 'Sales Transactions - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sales Transactions</h4>
        <p class="text-muted mb-0">Record and monitor customer sales orders and invoices</p>
    </div>
    @if(in_array(session('user_role'), ['SuperAdmin', 'Sales']))
    <a href="{{ route('sales.create') }}" class="btn btn-primary" id="btn-add_sales">
        <i class="bi bi-plus-circle me-1"></i> New Sale
    </a>
    @endif
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="table-sales">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Invoice Number</th>
                        <th>Date</th>
                        <th>Cashier / User</th>
                        <th class="text-center">Total Items</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center" style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Sale -->
<div class="modal fade" id="modal-detail_sales" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Sales Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 130px;">Invoice Number</th>
                                <td>: <span id="detail-number_sales" class="fw-bold"></span></td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>: <span id="detail-date_sales"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 130px;">Cashier / User</th>
                                <td>: <span id="detail-user_sales"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="table-detail_sales_items">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Code</th>
                                <th>Item Name</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Grand Total:</td>
                                <td class="text-end text-primary" id="detail-total_sales_amount">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.routes = {
        salesData: "{{ Route::has('api.sales.data') ? route('api.sales.data') : (Route::has('datatable.sales') ? route('datatable.sales') : url('/api/sales_datatables')) }}",
        salesBase: "{{ url('/api/sales') }}",
        userRole: "{{ session('user_role') }}"
    };
</script>
<script src="{{ asset('js/modules/sales.js') }}"></script>
@endpush
