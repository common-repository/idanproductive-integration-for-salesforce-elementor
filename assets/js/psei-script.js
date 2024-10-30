
jQuery(document).ready(function($){
    document.querySelectorAll('.popover-link').forEach(link => {
        link.addEventListener('mouseover', () => {

            let token = document.querySelector('#psei_access_token').value;
            let sObject = document.querySelector('#psei_sobject').value;
            let relationship = link.dataset.relationship;
            let record_id = link.dataset.record_id;

            let parentElement = link.parentElement.parentElement;


            if(parentElement.querySelector("#popover_relationship_name").innerHTML !== relationship){

                var data = JSON.stringify({
                    "access_token": token,
                    "sObject": sObject,
                    "relationship": relationship,
                    "record_id": record_id
                });

                var xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                
                xhr.addEventListener("readystatechange", function() {
                if(this.readyState === 4) {

                    let response = JSON.parse(this.responseText);

                    parentElement.querySelector("#loading").style.display = 'none';

                    parentElement.querySelector("#popover_relationship_name").innerHTML = relationship;

                    
                    let divToClone = parentElement.querySelector(".psei-profile-name-details");
                    for (const key of Object.keys(response)) {
                     
                        clonedDiv = divToClone.cloneNode(true);
                        
                        clonedDiv.style.display = 'block';

                        clonedDiv.querySelector(".psei-profile-text-value").innerHTML = response[key];
                        clonedDiv.querySelector(".psei-profile-input-label").innerHTML = key;
                        
                        if(typeof response[key] !== 'object') {
                            parentElement.querySelector('.grid-container').appendChild(clonedDiv);
                        }
                        
                    }

                }
                });

                const host = window.location.host;
                const protocol = window.location.protocol;
                console.log(protocol);
                console.log(host);
                
                xhr.open("POST", `${protocol}//${host}/wp-admin/admin-ajax.php?action=custom_ajax_request`);
                xhr.setRequestHeader("X-WP-Nonce", "d983424827");

                xhr.send(data);
            }


        })
    })


    // Submit login form

    $('.psei-login-button').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var form_data = form.serialize();
        var buttonHtml = $('.psei-login-button').html();
        $.ajax({
            type: 'POST',
            url: js_psei_login.ajax_url,
            dataType : 'json',
            data: {
                action: 'submit_login',
                security: js_psei_login.ajax_nonce,
                fields: form_data
            },
            beforeSend: function() {
                $('.psei-login-button').html('submitting...').attr('disabled', 'disabled');
                $('.psei-login-error').html('').addClass('hidden');
                
            },
            success: function(response) {
                if(response.status == 'success') {
                    window.location.href = response.data.redirect_url;
                } else {
                    $('.psei-login-error').html(response.message).removeClass('hidden');
                    $('.psei-login-button').html(buttonHtml).removeAttr('disabled');
                }
            },
            error: function(response) {
                console.log('error response',response);
                $('.psei-login-error').html(response.responseText).removeClass('hidden');
                $('.psei-login-button').html(buttonHtml).removeAttr('disabled');
            }
        })
    })

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
    })

    $('.psei-update-profile-button').on('click', function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var form_data = form.serialize();
        $.ajax({
            type: 'POST',
            url: js_psei_update_profile.ajax_url,
            dataType : 'json',
            data: {
                action: 'update_profile',
                security: js_psei_update_profile.ajax_nonce,
                fields: form_data
            },
            success: function(response) {
                document.querySelector('.psei-update-profile-error').style.display = 'block';
                $('.psei-update-profile-error').html(response.data.message);
            },
            error: function(response) {
                document.querySelector('.psei-update-profile-error').style.display = 'block';
                $('.psei-update-profile-error').html(response.data.message);
            }
        })
    });

   


    /* document.querySelector('.psei-toggle-password').on('click', function (e) {
        // toggle the type attribute
        const type = document.querySelector('.psei-login-password').getAttribute('type') === 'password' ? 'text' : 'password';
        document.querySelector('.psei-login-password').setAttribute('type', type);
        // toggle the eye / eye slash icon
        this.classList.toggle('bi-eye');
    }); */

    /* OTP Functionality */
    document.querySelectorAll('.otp-input').forEach((e) => {
        let currentInput = e;
        console.log(currentInput);
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
    
    if( document.querySelector("#psei-profile-btn-edit")) {
        document.querySelector("#psei-profile-btn-edit").addEventListener('click', () => {
            document.querySelector("#psei-profile-btn-edit").style.display = 'none';
            document.querySelector("#psei-profile-btn-cancel-edit").style.display = 'block';
    
           
            document.querySelector("#psei-profile-edit-info").style.display = 'block';
            document.querySelector("#psei-profile-view-info").style.display = 'none';
    
        })
    
        document.querySelector("#psei-profile-btn-cancel-edit").addEventListener('click', () => {
            document.querySelector("#psei-profile-btn-edit").style.display = 'block';
            document.querySelector("#psei-profile-btn-cancel-edit").style.display = 'none';
    
            document.querySelector("#psei-profile-edit-info").style.display = 'none';
            document.querySelector("#psei-profile-view-info").style.display = 'block';
        })
    }

    $('.psei-go-to-next-object-page').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('id');
        let recordIndex = id.replace(/psei-object-field-/,'');
        let form = $('#psei-record-form-'+reportID);
        let form_data = form.serialize();
        $.ajax({
            type: 'POST',
            url: js_psei_fetch_report.ajax_url,
            dataType : 'json',
            data: {
                action: 'psei_ajax_save_report_data',
                security: js_psei_fetch_report.ajax_nonce,
                fields: form_data
            },
            success: function(response) {
                setTimeout(function(){
                    window.location.reload();
                }, 2000);
            },
            error: function(response) {
                console.log('error response',response);
            }
        })
    })

    $('.psei-report-sidebar-fetch-data').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('id');
        let reportID = id.replace(/psei-sidebar-fetch-/,'');
        let form = $('#psei-report-sidebar-form-'+reportID);
        let form_data = form.serialize();
        $.ajax({
            type: 'POST',
            url: js_psei_fetch_report.ajax_url,
            dataType : 'json',
            data: {
                action: 'psei_ajax_save_report_data',
                security: js_psei_fetch_report.ajax_nonce,
                fields: form_data
            },
            success: function(response) {
                setTimeout(function(){
                    window.location.reload();
                }, 1000);
            },
            error: function(response) {
                console.log('report error', response)
            }
        })
    })
});

