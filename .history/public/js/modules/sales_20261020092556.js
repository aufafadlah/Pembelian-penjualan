/**
 * modules/sales.js
 * Handles Sales DataTable, detail modal, multi-item creation, stock checks, and editing
 */

$(document).ready(function () {
    const routes = window.routes || {};
    let salesItems = [];

    // --- Index Page Logic ---
    if ($('#table-sales').length) {
        const detailModalElement = document.getElementById('modal-detail_sales');
        const detailModal = detailModalElement ? new bootstrap.Modal(detailModalElement) : null;

        const salesDataTable = $('#table-sales').DataTable({
            processing: true,
            serverSide: true,
            pagingType: 'full_numbers',
            language: AppAjax.getDtLanguage(),
            ajax: {
                url: routes.salesData,
                type: 'GET'
            },
            dom: "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: AppAjax.getDtButtons('Sales Invoices Data'),
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
                                <a href="/sales/${id}/print" target="_blank" class="btn btn-secondary btn-sm" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                        `;

                        if (routes.userRole !== 'Manager') {
                            actionButtons += `
                                <a href="/sales/${id}/edit" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-danger btn-sm btn-delete_sales" data-id="${id}" title="Delete">
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
            const saleId = $(this).data('id');

            $.ajax({
                url: routes.salesBase + '/' + saleId,
                type: 'GET',
                success: function (response) {
                    const salesData = response.data || response;
                    $('#detail-number_sales').text(salesData.number);
                    $('#detail-date_sales').text(salesData.date);
                    $('#detail-user_sales').text(salesData.user_name);

                    let itemsHtml = '';
                    let grandTotal = 0;

                    if (salesData.items && salesData.items.length > 0) {
                        salesData.items.forEach((item, index) => {
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

                    $('#table-detail_sales_items tbody').html(itemsHtml);
                    $('#detail-total_sales_amount').text(AppAjax.formatRupiah(grandTotal));

                    if (detailModal) {
                        detailModal.show();
                    }
                },
                error: function (xhr) {
                    AppAjax.showError(xhr, 'Failed to load sales details');
                }
            });
        });

        $(document).on('click', '.btn-delete_sales', function () {
            const saleId = $(this).data('id');

            AppAjax.confirmAction({
                title: 'Delete Sales Transaction?',
                text: 'This will restore inventory stock and delete the record.',
                confirmText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: routes.salesBase + '/' + saleId,
                        type: 'DELETE',
                        success: function (response) {
                            salesDataTable.ajax.reload(null, false);
                            AppAjax.showSuccess(response.message);
                        },
                        error: function (xhr) {
                            AppAjax.showError(xhr, 'Failed to delete sale transaction');
                        }
                    });
                }
            });
        });
    }

    // --- Separate Form Page Logic ---
    if ($('#form-transaction_sales').length) {
        let inventoryList = [];

        function renderSalesTable() {
            const tableBody = $('#tbody-sales_items');
            if (salesItems.length === 0) {
                tableBody.html(`
                    <tr id="tr-empty_sales_items">
                        <td colspan="7" class="text-center py-4 text-muted">
                            No items added yet. Please select an item above and click "Add Item".
                        </td>
                    </tr>
                `);
                $('#span-total_sales_amount').text(AppAjax.formatRupiah(0));
                return;
            }

            let rowsHtml = '';
            let totalAmount = 0;

            salesItems.forEach((item, index) => {
                const subtotal = item.qty * item.price;
                totalAmount += subtotal;

                rowsHtml += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${item.inventory_code}</td>
                        <td>${item.inventory_name}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center input-row_qty" data-index="${index}" min="1" max="${item.available_stock}" value="${item.qty}">
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
            $('#span-total_sales_amount').text(AppAjax.formatRupiah(totalAmount));
        }

        // Initialize Select2 on inventory dropdown
        $('#select-inventory_item_sales').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Choose Item --',
            width: '100%'
        });

        // Fetch Inventory Items for Select2
        AppAjax.fetchInventoryItems(routes.inventoryItems, function (response) {
            inventoryList = response.data || [];
            const selectDropdown = $('#select-inventory_item_sales');
            selectDropdown.empty().append('<option value="">-- Choose Item --</option>');

            inventoryList.forEach(item => {
                selectDropdown.append(
                    `<option value="${item.id}" data-code="${item.code}" data-name="${item.name}" data-price="${item.price}" data-stock="${item.stock}">
                        ${item.code} - ${item.name} (Stock: ${item.stock})
                    </option>`
                );
            });

            // If in edit mode, load existing sale transaction
            const formMode = $('#input-form_mode').val();
            const saleId = $('#input-sale_id').val();
            if (formMode === 'edit' && saleId) {
                $.ajax({
                    url: routes.salesBase + '/' + saleId,
                    type: 'GET',
                    success: function (res) {
                        const data = res.data || res;
                        $('#input-number').val(data.number);
                        $('#input-date').val(data.date);

                        if (data.items && data.items.length > 0) {
                            salesItems = data.items.map(item => {
                                const matchedInv = inventoryList.find(inv => inv.id === item.inventory_id);
                                const availableStock = (matchedInv ? matchedInv.stock : 0) + item.qty;
                                return {
                                    inventory_id: item.inventory_id,
                                    inventory_code: item.inventory_code,
                                    inventory_name: item.inventory_name,
                                    qty: item.qty,
                                    price: item.price,
                                    available_stock: availableStock
                                };
                            });
                            renderSalesTable();
                        }
                    },
                    error: function (xhr) {
                        AppAjax.showError(xhr, 'Failed to load transaction data');
                    }
                });
            }
        });

        // Auto-fill price and stock display on item selected
        $('#select-inventory_item_sales').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const itemPrice = selectedOption.data('price') || 0;
            const itemStock = selectedOption.data('stock') !== undefined ? selectedOption.data('stock') : '-';
            $('#input-item_price').val(itemPrice);
            $('#input-item_quantity').val(1);
            $('#span-available_stock_sales').text('Available Stock: ' + itemStock);
        });

        // Add Item Button
        $('#btn-add_item_sales').on('click', function () {
            const selectedOption = $('#select-inventory_item_sales').find(':selected');
            const inventoryId = parseInt($('#select-inventory_item_sales').val());
            const inventoryCode = selectedOption.data('code') || '';
            const inventoryName = selectedOption.data('name') || '';
            const availableStock = parseInt(selectedOption.data('stock')) || 0;
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

            const existingIndex = salesItems.findIndex(i => i.inventory_id === inventoryId);
            const totalRequestedQty = (existingIndex > -1 ? salesItems[existingIndex].qty : 0) + itemQty;

            if (totalRequestedQty > availableStock) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Stock',
                    text: `Available stock is only ${availableStock} units, but requested total is ${totalRequestedQty} units.`
                });
                return;
            }

            if (existingIndex > -1) {
                salesItems[existingIndex].qty = totalRequestedQty;
                salesItems[existingIndex].price = itemPrice;
            } else {
                salesItems.push({
                    inventory_id: inventoryId,
                    inventory_code: inventoryCode,
                    inventory_name: inventoryName,
                    qty: itemQty,
                    price: itemPrice,
                    available_stock: availableStock
                });
            }

            $('#select-inventory_item_sales').val('').trigger('change');
            $('#input-item_price').val('');
            $('#input-item_quantity').val(1);
            $('#span-available_stock_sales').text('Available Stock: -');

            renderSalesTable();
        });

        // Change quantity directly in table
        $(document).on('change', '.input-row_qty', function () {
            const itemIndex = $(this).data('index');
            const newQty = parseInt($(this).val()) || 1;
            if (salesItems[itemIndex]) {
                if (newQty > salesItems[itemIndex].available_stock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Insufficient Stock',
                        text: `Available stock is only ${salesItems[itemIndex].available_stock} units.`
                    });
                    $(this).val(salesItems[itemIndex].qty);
                    return;
                }
                salesItems[itemIndex].qty = Math.max(1, newQty);
                renderSalesTable();
            }
        });

        // Change price directly in table
        $(document).on('change', '.input-row_price', function () {
            const itemIndex = $(this).data('index');
            const newPrice = parseFloat($(this).val()) || 0;
            if (salesItems[itemIndex]) {
                salesItems[itemIndex].price = Math.max(0, newPrice);
                renderSalesTable();
            }
        });

        // Remove item row
        $(document).on('click', '.btn-remove_item', function () {
            const itemIndex = $(this).data('index');
            salesItems.splice(itemIndex, 1);
            renderSalesTable();
        });

        // Form Submit
        $('#form-transaction_sales').on('submit', function (e) {
            e.preventDefault();

            if (salesItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Added',
                    text: 'Please add at least one item to save the sale.'
                });
                return;
            }

            const formMode = $('#input-form_mode').val();
            const saleId = $('#input-sale_id').val();
            const isEditing = formMode === 'edit' && Boolean(saleId);

            const submitUrl = isEditing ? routes.salesBase + '/' + saleId : routes.salesStore;
            const requestMethod = isEditing ? 'PUT' : 'POST';

            const payloadData = {
                number: $('#input-number').val(),
                date: $('#input-date').val(),
                items: salesItems.map(item => ({
                    inventory_id: item.inventory_id,
                    qty: item.qty,
                    price: item.price
                }))
            };

            const submitButton = $('#btn-save_sales');
            const originalButtonHtml = submitButton.html();
            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: submitUrl,
                type: requestMethod,
                contentType: 'application/json',
                data: JSON.stringify(payloadData),
                success: function (response) {
                    AppAjax.showSuccess(response.message, function () {
                        window.location.href = response.redirect || routes.salesIndex;
                    });
                },
                error: function (xhr) {
                    submitButton.prop('disabled', false).html(originalButtonHtml);
                    AppAjax.showError(xhr, 'Failed to save sales');
                }
            });
        });
    }
});
