@extends('layouts.main')
@section('title', 'Purchase Transactions - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Purchase Transactions</h4>
        <p class="text-muted mb-0">Record and track inventory purchase orders</p>
    </div>
    @if(in_array(session('user_role'), ['SuperAdmin', 'Purchase']))
    <a href="{{ route('purchases.create') }}" class="btn btn-primary" id="btn-add_purchase">
        <i class="bi bi-plus-circle me-1"></i> New Purchase
    </a>
    @endif
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="table-purchase">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Created By</th>
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

<!-- Modal Detail Purchase -->
<div class="modal fade" id="modal-detail_purchase" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 130px;">PO Number</th>
                                <td>: <span id="detail-number_purchase" class="fw-bold"></span></td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>: <span id="detail-date_purchase"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 130px;">Created By</th>
                                <td>: <span id="detail-user_purchase"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="table-detail_purchase_items">
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
                                <td class="text-end" id="detail-total_purchase_amount">Rp 0</td>
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
        purchaseData: "{{ Route::has('api.purchases.data') ? route('api.purchases.data') : (Route::has('datatable.purchases') ? route('datatable.purchases') : url('/api/purchases_datatables')) }}",
        purchaseBase: "{{ url('/api/purchases') }}",
        purchaseStory: "{{ Route::has('api.inventories.store') ? route('api.inventories.store') : (Route::has('post.inventories') ? route('post.inventories') : url('/api/purchases')) }}"
        userRole: "{{ session('user_role') }}"
    };
</script>
<script src="{{ asset('js/modules/purchases.js') }}"></script>

@endpush
