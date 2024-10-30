jQuery(document).ready(function($) {
    console.log('loaded')
    $('.psei-go-to-next-object').on('click', function(e) {
        console.log('clicked');
        e.preventDefault();
        var form = $(this).closest('form');
        var form_data = form.serialize();
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
})