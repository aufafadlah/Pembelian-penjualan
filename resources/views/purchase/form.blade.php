@extends('layouts.main')
@section('title', ($mode === 'edit' ? 'Edit' : 'Create') . ' Purchase - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $mode === 'edit' ? 'Edit Purchase Transaction' : 'Create Purchase Transaction' }}</h4>
        <p class="text-muted mb-0">{{ $mode === 'edit' ? 'Update existing purchase details and inventory quantities' : 'Record a new purchase order with multiple items' }}</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary" id="btn-cancel">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
</div>

<form id="form-transaction_purchase">
    @csrf
    <input type="hidden" name="purchase_id" id="input-purchase_id" value="{{ $purchase_id }}">
    <input type="hidden" id="input-form_mode" value="{{ $mode }}">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-info-circle me-1 text-primary"></i> Transaction Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="input-number" class="form-label fw-semibold">PO Number</label>
                    <input type="text" class="form-control" name="number" id="input-number" placeholder="Leave blank for automatic generation">
                </div>
                <div class="col-md-6">
                    <label for="input-date" class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="date" id="input-date" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-cart-plus me-1 text-success"></i> Add Purchase Items
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="select-inventory_item_purchase" class="form-label fw-semibold">Select Inventory Item</label>
                    <select class="form-select select2-inventory" id="select-inventory_item_purchase">
                        <option value="">-- Choose Item --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="input-item_price" class="form-label fw-semibold">Purchase Price (Rp)</label>
                    <input type="number" step="0.01" class="form-control" id="input-item_price" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label for="input-item_quantity" class="form-label fw-semibold">Quantity</label>
                    <input type="number" class="form-control" id="input-item_quantity" min="1" value="1">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success w-100" id="btn-add_item_purchase">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </div>
            </div>

            <hr class="my-4">

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="table-item_purchase_list">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Code</th>
                            <th>Item Name</th>
                            <th class="text-center" style="width: 120px;">Qty</th>
                            <th class="text-end" style="width: 180px;">Unit Price (Rp)</th>
                            <th class="text-end" style="width: 180px;">Subtotal (Rp)</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-purchase_items">
                        <tr id="tr-empty_purchase_items">
                            <td colspan="7" class="text-center py-4 text-muted">
                                No items added yet. Please select an item above and click "Add Item".
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fs-5 fw-bold">
                            <td colspan="5" class="text-end">Total Amount:</td>
                            <td class="text-end text-success" id="span-total_purchase_amount">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btn-save_purchase">
            <i class="bi bi-save me-1"></i> {{ $mode === 'edit' ? 'Update Purchase' : 'Save Purchase' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    window.routes = {
        inventoryItems: "{{ Route::has('api.inventories.items') ? route('api.inventories.items') : url('/api/inventories') }}",
        purchaseStore: "{{ Route::has('api.purchases.store') ? route('api.purchases.store') : (Route::has('post.purchases') ? route('post.purchases') : url('/api/purchases')) }}",
        purchaseBase: "{{ url('/api/purchases') }}",
        purchaseIndex: "{{ route('purchases.index') }}"
    };
</script>
<script src="{{ asset('js/modules/purchases.js') }}"></script>
@endpush
