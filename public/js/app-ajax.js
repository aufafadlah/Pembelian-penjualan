/**
 * app-ajax.js
 * Centralized AJAX configurations, shared helpers, and SweetAlert2 utilities
 */

const AppAjax = {
    // Initialize default AJAX jQuery setup with CSRF Token and Accept headers
    init: function () {
        $.ajaxSetup({
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });
    },

    // Format number to Indonesian Rupiah currency format
    formatRupiah: function (number) {
        return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
    },

    // DataTables arrow pagination and language configurations
    getDtLanguage: function () {
        return {
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>'
            }
        };
    },

    // DataTables export button configurations (Excel, CSV, PDF, Print)
    getDtButtons: function (title) {
        return [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: title,
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn btn-info btn-sm text-white',
                title: title,
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: title,
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn btn-secondary btn-sm',
                title: title,
                exportOptions: { columns: ':not(:last-child)' }
            }
        ];
    },

    // Success notification using SweetAlert2
    showSuccess: function (message, callback) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 1600,
            showConfirmButton: false
        }).then(() => {
            if (typeof callback === 'function') {
                callback();
            }
        });
    },

    // Error notification handling Laravel JSON responses (422 validation or 4xx/5xx errors)
    showError: function (xhr, defaultTitle = 'An Error Occurred') {
        let errorMessage = 'Failed to process request.';

        if (xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                const errorEntries = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errorEntries.join('<br>');
            } else if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
        }

        Swal.fire({
            icon: 'error',
            title: defaultTitle,
            html: errorMessage
        });
    },

    // Confirmation dialog for delete or irreversible actions
    confirmAction: function (options) {
        return Swal.fire({
            title: options.title || 'Are you sure?',
            text: options.text || 'This action cannot be undone.',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmColor || '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: options.confirmText || 'Yes, Proceed!',
            cancelButtonText: options.cancelText || 'Cancel'
        });
    },

    // Shared API: Fetch inventory items for Select2 dropdowns
    fetchInventoryItems: function (url, callback) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (data) {
                if (typeof callback === 'function') {
                    callback(data);
                }
            },
            error: function (xhr) {
                AppAjax.showError(xhr, 'Failed to fetch inventory items');
            }
        });
    }
};

$(document).ready(function () {
    AppAjax.init();
});
