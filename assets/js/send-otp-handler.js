
class PSEISendOtpHandler extends elementorModules.frontend.handlers.Base {

    otpType = '';

    psei_setOtpType() {
        this.otpType = this.getElementSettings('otp_type');
    }

    psei_sendOtpCode() {
       this.setOtpType();

       if( this.otpType === 'email' ) {

        // generate 6 digit OTP
        var otp = Math.floor(100000 + Math.random() * 900000);

        // get email address

        // do http request
        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/wp-admin/admin-ajax.php?action=send_otp', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({ email: 'Your otp code is '+otp})); // send email to server

       }
    }

    onInit() {
        // this.sendOtpCode();
    }

    /**
	 * On Element Change
	 *
	 * Runs every time a control value is changed by the user in the editor.
	 *
	 * @param {string} propertyName - The ID of the control that was changed.
	 */
	onElementChange( propertyName ) {
		if ( 'otp_type' === propertyName ) {
			this.psei_setOtpType();
		}
	}
}