<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
require_once( PSEI_INCLUDES_PATH . 'salesforce.php' );

use Salesforce_Elementor_Addon\Salesforce;


function psei_submit_login() {
    check_ajax_referer( 'psei_login_nonce_action', 'security' );
    if ( ! current_user_can('administrator') ) {
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $next_page = sanitize_text_field($_POST['next_page']);
        $saved_salesforce_token = get_option('psei_salesforce_token');
        $saved_salesforce_username = get_option('psei_salesforce_username');
        $saved_salesforce_password = get_option('psei_salesforce_password');
        $saved_salesforce_client_id = get_option('psei_salesforce_client_id');
        $saved_salesforce_client_secret = get_option('psei_salesforce_client_secret');
        $saved_salesforce_instance_url = get_option('psei_salesforce_instance_url');
        $saved_salesforce_access_token = get_option('psei_salesforce_access_token');
        $salesforce = Salesforce::instance(
            $saved_salesforce_client_id, 
            $saved_salesforce_client_secret,
            $saved_salesforce_username, 
            $saved_salesforce_password, 
            $saved_salesforce_token, 
            $saved_salesforce_instance_url, 
            $saved_salesforce_access_token
        );
        $result = $salesforce->login($phone_number);

        if($result['data']){
            // navigate to next page
            wp_redirect($next_page);
        }
    }
   

}

// add_action('wp_ajax_submit_login', 'psei_submit_login');
