@extends('layouts.main')
@section('title', ($mode === 'edit' ? 'Edit' : 'Create') . ' Sale - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $mode === 'edit' ? 'Edit Sales Transaction' : 'Create Sales Transaction' }}</h4>
        <p class="text-muted mb-0">{{ $mode === 'edit' ? 'Update existing sales details and manage stock allocation' : 'Create a new sales invoice with multiple items' }}</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary" id="btn-cancel">
        <i class="bi bi-arrow-left me-1"></i> Back to Sales
    </a>
</div>

<form id="form-transaction_sales">
    @csrf
    <input type="hidden" name="sale_id" id="input-sale_id" value="{{ $sale_id }}">
    <input type="hidden" id="input-form_mode" value="{{ $mode }}">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-info-circle me-1 text-primary"></i> Invoice Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="input-number" class="form-label fw-semibold">Invoice Number</label>
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
        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-cart-plus me-1 text-info"></i> Add Sales Items</span>
            <span class="badge bg-light text-dark border" id="span-available_stock_sales">Available Stock: -</span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="select-inventory_item_sales" class="form-label fw-semibold">Select Inventory Item</label>
                    <select class="form-select select2-inventory" id="select-inventory_item_sales">
                        <option value="">-- Choose Item --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="input-item_price" class="form-label fw-semibold">Selling Price (Rp)</label>
                    <input type="number" step="0.01" class="form-control" id="input-item_price" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label for="input-item_quantity" class="form-label fw-semibold">Quantity</label>
                    <input type="number" class="form-control" id="input-item_quantity" min="1" value="1">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="btn-add_item_sales">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </div>
            </div>

            <hr class="my-4">

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="table-item_sales_list">
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
                    <tbody id="tbody-sales_items">
                        <tr id="tr-empty_sales_items">
                            <td colspan="7" class="text-center py-4 text-muted">
                                No items added yet. Please select an item above and click "Add Item".
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fs-5 fw-bold">
                            <td colspan="5" class="text-end">Grand Total:</td>
                            <td class="text-end text-primary" id="span-total_sales_amount">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btn-save_sales">
            <i class="bi bi-save me-1"></i> {{ $mode === 'edit' ? 'Update Sale' : 'Save Sale' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    window.routes = {
        inventoryItems: "{{ Route::has('api.inventories.items') ? route('api.inventories.items') : url('/api/inventories') }}",
        salesStore: "{{ Route::has('api.sales.store') ? route('api.sales.store') : (Route::has('post.sales') ? route('post.sales') : url('/api/sales')) }}",
        salesBase: "{{ url('/api/sales') }}",
        salesIndex: "{{ route('sales.index') }}"
    };
</script>
<script src="{{ asset('js/modules/sales.js') }}"></script>
@endpush
