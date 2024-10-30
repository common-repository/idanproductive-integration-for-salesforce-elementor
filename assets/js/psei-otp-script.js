jQuery(document).ready(function($) {
    $('.psei-otp-button').on('click', function(e) {
        let otp_string = "";

        document.querySelectorAll('.otp-input').forEach((e) => {
            otp_string = otp_string + e.value;
        });
        console.log('Test Clicked: ' + otp_string);

        e.preventDefault();
        var form = $(this).closest('form');
        var form_data = form.serialize();
        let post_params = new URLSearchParams(form_data);

        console.log(form_data);

        var buttonHtml = $('.psei-otp-button').html()

        $.ajax({
            type: 'POST',
            url: js_psei_otp_verify.ajax_url,
            dataType : 'json',
            data: {
                action: 'submit_otp',
                security: js_psei_otp_verify.ajax_nonce,
                fields: {
                    otp_code: otp_string,
                    next_page: post_params.get('next_page')
                }
            },
            beforeSend: function() {
                $('.psei-otp-button').html('submitting...').attr('disabled', 'disabled');
                $('.psei-otp-error').html('').addClass('hidden');
            },
            success: function(response) {
                if(response.status == 'success') {
                    window.location.href = response.data.redirect_url;
                } else {
                    $('.psei-otp-error').html(response.data.message).removeClass('hidden');
                    $('.psei-otp-button').html(buttonHtml).removeAttr('disabled');
                }
            },
            error: function(response) {
                $('.psei-otp-error').html(response.responseText).removeClass('hidden');
                $('.psei-otp-button').html(buttonHtml).removeAttr('disabled');
            }
        })
    });

    /* OTP Input Functionality */
    document.querySelectorAll('.otp-input').forEach((e) => {
        let currentInput = e;
        currentInput.setAttribute('maxlength', 1);
        currentInput.addEventListener('keyup', function(e) {
            var parent = $(currentInput).parent();
            
            if(e.keyCode === 8 || e.keyCode === 37) {
                var prev = parent.find('input#' + $(this).data('previous'));
                
                if(prev.length) {
                    $(prev).select();
                }
            } else if((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e.keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) {
                var next = parent.find('input#' + $(this).data('next'));
                
                if(next.length) {
                    $(next).select();
                } else {
                    if(parent.data('autosubmit')) {
                        parent.submit();
                    }
                }
            }
        });
    });
});