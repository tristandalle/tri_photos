$(function () {
    var error = $('.ajax-error');
    $('#registration_email').on('blur', function () {
        var email = $('#registration_email').val();
        if (email == '') {
            return;
        }
        $.ajax({
            url: 'check-ajax-email',
            type: 'post',
            data: {
                'email': email
            },
            success: function (response) {
                var taken = JSON.parse(response).taken;
                if (taken == 1) {
                    error.show();
                    $('#registration_email').addClass('is-invalid');
                } else {
                    error.hide();
                    $('#registration_email').removeClass('is-invalid');
                }
            }
        });
    });
});