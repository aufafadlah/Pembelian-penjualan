/**
 * modules/purchases.js
 * Handles Purchase DataTable, detail modal, multi-item creation, and editing
 */

$(document).ready(function () {
    const routes = window.routes || {};
    let purchaseItems = [];

    // --- Index Page Logic ---
    if ($('#table-purchase').length) {
        const detailModalElement = document.getElementById('modal-detail_purchase');
        const detailModal = detailModalElement ? new bootstrap.Modal(detailModalElement) : null;

        const purchaseDataTable = $('#table-purchase').DataTable({
            processing: true,
            serverSide: true,
            pagingType: 'full_numbers',
            language: AppAjax.getDtLanguage(),
            ajax: {
                url: routes.purchaseData,
                type: 'GET'
            },
            dom: "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: AppAjax.getDtButtons('Purchase Orders Data'),
            columns: [
                { data: 'row_number', className: 'text-center' },
                { data: 'number', className: 'fw-semibold' },
                { data: 'date', className: 'text-center' },
                { data: 'user_name' },
                { data: 'item_count', className: 'text-center' },
                {
                    data: 'total_amount',
                    className: 'text-end fw-semibold',
                    render: function (data) {
                        return AppAjax.formatRupiah(data);
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (id) {
                        let actionButtons = `
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-info btn-sm text-white btn-view_detail" data-id="${id}" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="/purchases/${id}/print" target="_blank" class="btn btn-secondary btn-sm" title="Print PO">
                                    <i class="bi bi-printer"></i>
                                </a>
                        `;

                        if (routes.userRole !== 'Manager') {
                            actionButtons += `
                                <a href="/purchases/${id}/edit" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-danger btn-sm btn-delete_purchase" data-id="${id}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }

                        actionButtons += `</div>`;
                        return actionButtons;
                    }
                }
            ]
        });

        $(document).on('click', '.btn-view_detail', function () {
            const purchaseId = $(this).data('id');

            $.ajax({
                url: routes.purchaseBase + '/' + purchaseId,
                type: 'GET',
                success: function (response) {
                    const purchaseData = response.data || response;
                    $('#detail-number_purchase').text(purchaseData.number);
                    $('#detail-date_purchase').text(purchaseData.date);
                    $('#detail-user_purchase').text(purchaseData.user_name);

                    let itemsHtml = '';
                    let grandTotal = 0;

                    if (purchaseData.items && purchaseData.items.length > 0) {
                        purchaseData.items.forEach((item, index) => {
                            grandTotal += item.subtotal;
                            itemsHtml += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>${item.inventory_code}</td>
                                    <td>${item.inventory_name}</td>
                                    <td class="text-center">${item.qty}</td>
                                    <td class="text-end">${AppAjax.formatRupiah(item.price)}</td>
                                    <td class="text-end fw-semibold">${AppAjax.formatRupiah(item.subtotal)}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#table-detail_purchase_items tbody').html(itemsHtml);
                    $('#detail-total_purchase_amount').text(AppAjax.formatRupiah(grandTotal));

                    if (detailModal) {
                        detailModal.show();
                    }
                },
                error: function (xhr) {
                    AppAjax.showError(xhr, 'Failed to load purchase details');
                }
            });
        });

        $(document).on('click', '.btn-delete_purchase', function () {
            const purchaseId = $(this).data('id');

            AppAjax.confirmAction({
                title: 'Delete Purchase Order?',
                text: 'This will reverse inventory stock additions and delete the record.',
                confirmText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: routes.purchaseBase + '/' + purchaseId,
                        type: 'DELETE',
                        success: function (response) {
                            purchaseDataTable.ajax.reload(null, false);
                            AppAjax.showSuccess(response.message);
                        },
                        error: function (xhr) {
                            AppAjax.showError(xhr, 'Failed to delete purchase order');
                        }
                    });
                }
            });
        });
    }

    // --- Separate Form Page Logic ---
    if ($('#form-transaction_purchase').length) {
        let inventoryList = [];

        function renderPurchaseTable() {
            const tableBody = $('#tbody-purchase_items');
            if (purchaseItems.length === 0) {
                tableBody.html(`
                    <tr id="tr-empty_purchase_items">
                        <td colspan="7" class="text-center py-4 text-muted">
                            No items added yet. Please select an item above and click "Add Item".
                        </td>
                    </tr>
                `);
                $('#span-total_purchase_amount').text(AppAjax.formatRupiah(0));
                return;
            }

            let rowsHtml = '';
            let totalAmount = 0;

            purchaseItems.forEach((item, index) => {
                const subtotal = item.qty * item.price;
                totalAmount += subtotal;

                rowsHtml += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${item.inventory_code}</td>
                        <td>${item.inventory_name}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center input-row_qty" data-index="${index}" min="1" value="${item.qty}">
                        </td>
                        <td class="text-end">
                            <input type="number" step="0.01" class="form-control form-control-sm text-end input-row_price" data-index="${index}" min="0" value="${item.price}">
                        </td>
                        <td class="text-end fw-semibold">${AppAjax.formatRupiah(subtotal)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove_item" data-index="${index}" title="Remove Item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableBody.html(rowsHtml);
            $('#span-total_purchase_amount').text(AppAjax.formatRupiah(totalAmount));
        }

        // Initialize Select2 on inventory dropdown
        $('#select-inventory_item_purchase').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Choose Item --',
            width: '100%'
        });

        // Fetch Inventory Items for Select2
        AppAjax.fetchInventoryItems(routes.inventoryItems, function (response) {
            inventoryList = response.data || [];
            const selectDropdown = $('#select-inventory_item_purchase');
            selectDropdown.empty().append('<option value="">-- Choose Item --</option>');

            inventoryList.forEach(item => {
                selectDropdown.append(
                    `<option value="${item.id}" data-code="${item.code}" data-name="${item.name}" data-price="${item.price}" data-stock="${item.stock}">
                        ${item.code} - ${item.name} (Stock: ${item.stock})
                    </option>`
                );
            });

            // If in edit mode, load existing purchase transaction
            const formMode = $('#input-form_mode').val();
            const purchaseId = $('#input-purchase_id').val();
            if (formMode === 'edit' && purchaseId) {
                $.ajax({
                    url: routes.purchaseBase + '/' + purchaseId,
                    type: 'GET',
                    success: function (res) {
                        const data = res.data || res;
                        $('#input-number').val(data.number);
                        $('#input-date').val(data.date);

                        if (data.items && data.items.length > 0) {
                            purchaseItems = data.items.map(item => ({
                                inventory_id: item.inventory_id,
                                inventory_code: item.inventory_code,
                                inventory_name: item.inventory_name,
                                qty: item.qty,
                                price: item.price
                            }));
                            renderPurchaseTable();
                        }
                    },
                    error: function (xhr) {
                        AppAjax.showError(xhr, 'Failed to load transaction data');
                    }
                });
            }
        });

        // Auto-fill price on item selected
        $('#select-inventory_item_purchase').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const itemPrice = selectedOption.data('price') || 0;
            $('#input-item_price').val(itemPrice);
            $('#input-item_quantity').val(1);
        });

        // Add Item Button
        $('#btn-add_item_purchase').on('click', function () {
            const selectedOption = $('#select-inventory_item_purchase').find(':selected');
            const inventoryId = parseInt($('#select-inventory_item_purchase').val());
            const inventoryCode = selectedOption.data('code') || '';
            const inventoryName = selectedOption.data('name') || '';
            const itemPrice = parseFloat($('#input-item_price').val()) || 0;
            const itemQty = parseInt($('#input-item_quantity').val()) || 0;

            if (!inventoryId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Item Required',
                    text: 'Please select an inventory item first.'
                });
                return;
            }

            if (itemQty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Quantity',
                    text: 'Quantity must be at least 1.'
                });
                return;
            }

            if (itemPrice < 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Price',
                    text: 'Price cannot be negative.'
                });
                return;
            }

            // Check if already added
            const existingIndex = purchaseItems.findIndex(i => i.inventory_id === inventoryId);
            if (existingIndex > -1) {
                purchaseItems[existingIndex].qty += itemQty;
                purchaseItems[existingIndex].price = itemPrice;
            } else {
                purchaseItems.push({
                    inventory_id: inventoryId,
                    inventory_code: inventoryCode,
                    inventory_name: inventoryName,
                    qty: itemQty,
                    price: itemPrice
                });
            }

            $('#select-inventory_item_purchase').val('').trigger('change');
            $('#input-item_price').val('');
            $('#input-item_quantity').val(1);

            renderPurchaseTable();
        });

        // Change quantity directly in table
        $(document).on('change', '.input-row_qty', function () {
            const itemIndex = $(this).data('index');
            const newQty = parseInt($(this).val()) || 1;
            if (purchaseItems[itemIndex]) {
                purchaseItems[itemIndex].qty = Math.max(1, newQty);
                renderPurchaseTable();
            }
        });

        // Change price directly in table
        $(document).on('change', '.input-row_price', function () {
            const itemIndex = $(this).data('index');
            const newPrice = parseFloat($(this).val()) || 0;
            if (purchaseItems[itemIndex]) {
                purchaseItems[itemIndex].price = Math.max(0, newPrice);
                renderPurchaseTable();
            }
        });

        // Remove item row
        $(document).on('click', '.btn-remove_item', function () {
            const itemIndex = $(this).data('index');
            purchaseItems.splice(itemIndex, 1);
            renderPurchaseTable();
        });

        // Form Submit
        $('#form-transaction_purchase').on('submit', function (e) {
            e.preventDefault();

            if (purchaseItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Added',
                    text: 'Please add at least one item to save the purchase.'
                });
                return;
            }

            const formMode = $('#input-form_mode').val();
            const purchaseId = $('#input-purchase_id').val();
            const isEditing = formMode === 'edit' && Boolean(purchaseId);

            const submitUrl = isEditing ? routes.purchaseBase + '/' + purchaseId : routes.purchaseStore;
            const requestMethod = isEditing ? 'PUT' : 'POST';

            const payloadData = {
                number: $('#input-number').val(),
                date: $('#input-date').val(),
                items: purchaseItems.map(item => ({
                    inventory_id: item.inventory_id,
                    qty: item.qty,
                    price: item.price
                }))
            };

            const submitButton = $('#btn-save_purchase');
            const originalButtonHtml = submitButton.html();
            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: submitUrl,
                type: requestMethod,
                contentType: 'application/json',
                data: JSON.stringify(payloadData),
                success: function (response) {
                    AppAjax.showSuccess(response.message, function () {
                        window.location.href = response.redirect || routes.purchaseIndex;
                    });
                },
                error: function (xhr) {
                    submitButton.prop('disabled', false).html(originalButtonHtml);
                    AppAjax.showError(xhr, 'Failed to save purchase');
                }
            });
        });
    }
});
