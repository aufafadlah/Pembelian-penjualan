/**
 * modules/inventories.js
 * Handles Inventory DataTable, modal CRUD, and AJAX operations
 */

$(document).ready(function () {
    const modalElement = new bootstrap.Modal(document.getElementById('modal-form_inventory'));
    const routes = window.routes || {};

    const inventoryDataTable = $('#table-inventory').DataTable({
        processing: true,
        serverSide: true,
        pagingType: 'full_numbers',
        language: AppAjax.getDtLanguage(),
        ajax: {
            url: routes.inventoryData,
            type: 'GET'
        },
        dom: "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
        buttons: AppAjax.getDtButtons('Master Inventory Data'),
        columns: [
            { data: 'row_number', className: 'text-center' },
            { data: 'code' },
            { data: 'name' },
            {
                data: 'price',
                className: 'text-end fw-semibold',
                render: function (data) {
                    return AppAjax.formatRupiah(data);
                }
            },
            { data: 'stock', className: 'text-center' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (id) {
                    return `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-warning btn-sm btn-edit" data-id="${id}" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-delete" data-id="${id}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#btn-add_inventory').on('click', function () {
        $('#form-inventory')[0].reset();
        $('#input-id').val('');
        $('#modal-title_inventory').text('Add New Item');
        modalElement.show();
    });

    $(document).on('click', '.btn-edit', function () {
        const itemId = $(this).data('id');
        $('#form-inventory')[0].reset();
        $('#input-id').val(itemId);
        $('#modal-title_inventory').text('Edit Inventory Item');

        $.ajax({
            url: routes.inventoryBase + '/' + itemId,
            type: 'GET',
            success: function (response) {
                const itemData = response.data || response;
                $('#input-code').val(itemData.code);
                $('#input-name').val(itemData.name);
                $('#input-price').val(itemData.price);
                $('#input-stock').val(itemData.stock);
                modalElement.show();
            },
            error: function (xhr) {
                AppAjax.showError(xhr, 'Failed to fetch inventory item');
            }
        });
    });

    $('#form-inventory').on('submit', function (e) {
        e.preventDefault();

        const itemId = $('#input-id').val();
        const isEditing = Boolean(itemId);
        const submitUrl = isEditing ? routes.inventoryBase + '/' + itemId : routes.inventoryStore;
        const requestMethod = isEditing ? 'PUT' : 'POST';

        const submitButton = $('#btn-save');
        const originalButtonHtml = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: submitUrl,
            type: requestMethod,
            data: $(this).serialize(),
            success: function (response) {
                submitButton.prop('disabled', false).html(originalButtonHtml);
                modalElement.hide();
                inventoryDataTable.ajax.reload(null, false);
                AppAjax.showSuccess(response.message);
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html(originalButtonHtml);
                AppAjax.showError(xhr, 'Validation Failed');
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const itemId = $(this).data('id');

        AppAjax.confirmAction({
            title: 'Delete Inventory Item?',
            text: 'This item will be soft deleted from the inventory.',
            confirmText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.inventoryBase + '/' + itemId,
                    type: 'DELETE',
                    success: function (response) {
                        inventoryDataTable.ajax.reload(null, false);
                        AppAjax.showSuccess(response.message);
                    },
                    error: function (xhr) {
                        AppAjax.showError(xhr, 'Failed to delete item');
                    }
                });
            }
        });
    });
});
