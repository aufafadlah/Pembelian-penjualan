@extends('layouts.main')
@section('title', 'Master Inventory - Sales & Purchases Application')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Master Inventory</h4>
        <p class="text-muted mb-0">Manage product items, pricing, and stock levels</p>
    </div>
    <button class="btn btn-primary" id="btn-add_inventory">
        <i class="bi bi-plus-circle me-1"></i> Add Item
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="table-inventory">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Code</th>
                        <th>Item Name</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pop-up for Create & Edit -->
<div class="modal fade" id="modal-form_inventory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-title_inventory">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-inventory">
                @csrf
                <input type="hidden" name="inventory_id" id="input-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="input-code" class="form-label fw-semibold">Item Code</label>
                        <input type="text" class="form-control" name="code" id="input-code" placeholder="Leave empty for auto generation">
                    </div>
                    <div class="mb-3">
                        <label for="input-name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="input-name" required placeholder="Enter item name">
                    </div>
                    <div class="mb-3">
                        <label for="input-price" class="form-label fw-semibold">Price (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="price" id="input-price" required placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label for="input-stock" class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="stock" id="input-stock" required min="0" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">
                        <i class="bi bi-save me-1"></i> Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.routes = {
        inventoryData: "{{ Route::has('api.inventories.data') ? route('api.inventories.data') : (Route::has('datatable.inventories') ? route('datatable.inventories') : url('/api/inventories_datatables')) }}",
        inventoryBase: "{{ url('/api/inventories') }}",
        inventoryStore: "{{ Route::has('api.inventories.store') ? route('api.inventories.store') : (Route::has('post.inventories') ? route('post.inventories') : url('/api/inventories')) }}"
    };
</script>
<script src="{{ asset('js/modules/inventories.js') }}"></script>
@endpush
