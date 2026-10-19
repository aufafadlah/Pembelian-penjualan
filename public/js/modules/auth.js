/**
 * modules/auth.js
 * Handles authentication, select2 user dropdown, and AJAX login submission
 */

$(document).ready(function () {
    if ($('.select2-user').length) {
        $('.select2-user').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Select User Account --',
            width: '100%'
        });
    }

    $('#form-login').on('submit', function (e) {
        e.preventDefault();

        const submitButton = $('#btn-login');
        const originalButtonHtml = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Signing In...');

        const loginUrl = window.routes?.loginProcess || '/api/login';

        $.ajax({
            url: loginUrl,
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    AppAjax.showSuccess(response.message, function () {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html(originalButtonHtml);
                AppAjax.showError(xhr, 'Login Failed');
            }
        });
    });
});
