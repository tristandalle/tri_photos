$(function () {
    var email_state = false;
    var error = $('.ajax-error');
    $('#registration_email').on('blur', function () {
        var email = $('#registration_email').val();
        if (email == '') {
            email_state = false;
            return;
        }
        $.ajax({
            url: 'check-ajax-email',
            type: 'post',
            data: {
                'email_check': 1,
                'email': email,
            },
            success: function (response) {
                if (response == 'taken') {
                    email_state = false;
                    error.show();
                    $('#registration_email').addClass('is-invalid');
                } else if (response == 'not_taken') {
                    email_state = true;
                    error.hide();
                    $('#registration_email').removeClass('is-invalid');
                }
            }
        });
    });
});