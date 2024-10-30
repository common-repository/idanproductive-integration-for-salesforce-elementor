<?php
namespace Productive_Salesforce_Elementor_Integration;

/**
 * Dashboard Settings Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Settings {
    /** 
	 * Saved Salesforce Token
	 * @var array
	 * @since 1.0
	 */

    public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_exad_ajax_save_elements_setting', array( $this, 'ajax_save_elements_setting_function' ) );
		add_action( 'wp_ajax_elemetix_ajax_clear_cache', array( $this, 'elemetix_ajax_clear_cache' ) );
	}

    /**
	 * Loading required scripts
	 * @param
	 * @return void
	 * @since 1.0.1
	 */	
	public function enqueue_admin_scripts( ) {

        wp_enqueue_style( 'exad-notice-css', PSEI_ADMIN_URL . 'assets/css/exad-notice.min.css' );
		wp_enqueue_style( 'exad-admin-css', PSEI_ADMIN_URL . 'assets/css/exad-admin.css' );
        wp_enqueue_script( 'exad-admin-js', PSEI_ADMIN_URL . 'assets/js/exad-admin.js', array( 'jquery', 'wp-color-picker' ), PSEI_PLUGIN_VERSION, true );

	}

    /**
	 * Create an admin menu.
	 * @param
	 * @return void
	 * @since 1.0.1
	 */
	public function create_admin_menu() {

		$title = __( 'Elemetix', 'elemetix' );
		add_menu_page( $title, $title, 'manage_options', 'elemetix', array( $this, 'admin_settings_page' ), 'dashicons-database', 26 );
		
	}

    /**
	 * Create settings page.
	 * @param
	 * @return void
	 * @since 1.0.1
	 */
	public function admin_settings_page() {

		$js_info = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'psei_settings_nonce_action' )
		);
		wp_localize_script( 'exad-admin-js', 'js_exad_settings', $js_info );	
        
        ?>
        <div class="exad-elements-dashboard-wrapper">
            <form action="" method="POST" id="exad-elements-settings" name="exad-elements-settings">

                <?php wp_nonce_field( 'save_dashboard_settings_nonce_action' ); ?>
                
                <div class="exad-dashboard-header-wrapper">
                    <div class="exad-dashboard-header-left">
                        <h2 class="title">
                            <?php echo esc_html__( 'Elemetix - Integration for Salesforce and Elementor', 'elemetix' ); ?>
                        </h2>
                    </div>
                    <div class="exad-dashboard-header-right">
                        <button type="submit" class="exad-btn exad-js-element-save-setting">
                            <?php echo esc_html__('Save Settings', 'elemetix'); ?>
                        </button>
						<button type="button" class="exad-btn exad-js-element-sync-cache" style="margin-left: 10px;">
                            <?php echo esc_html__('Sync', 'elemetix'); ?>
                        </button>
                    </div>
                </div>

				<div id="exad-sync-message" class="exad-message"></div>

                <div class="exad-dashboard-tabs-wrapper">
                    <?php include_once PSEI_ADMIN . 'templates/general.php'; ?>
                </div>
            </form> <!-- Form End -->
        </div>
    <?php

	}

    /**
	 * Saving widgets status with ajax request
	 * @param
	 * @return  array
	 * @since 1.0.1
	 */
	public function ajax_save_elements_setting_function() {

		check_ajax_referer( 'psei_settings_nonce_action', 'security' );

		if( isset( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $settings );
		} else {
			return;
		}

        update_option( 'psei_salesforce_token', $settings['salesforce_token'] );
        update_option( 'psei_salesforce_instance_url', $settings['salesforce_instance_url'] );
        update_option( 'psei_salesforce_username', $settings['salesforce_username'] );
        update_option( 'psei_salesforce_password', $settings['salesforce_password'] );
        update_option( 'psei_salesforce_client_id', $settings['salesforce_client_id'] );
        update_option( 'psei_salesforce_client_secret', $settings['salesforce_client_secret'] );
        update_option( 'psei_salesforce_login_table', $settings['salesforce_login_table'] );
        
		wp_die();                          
			
	}

	/**
	 * Clear Transient Cache by clicking SYNC Button
	 * @param
	 * @return  array
	 * @since 1.0.4
	 */
	public function elemetix_ajax_clear_cache() {
		check_ajax_referer( 'psei_settings_nonce_action', 'security' );

		global $wpdb;

		// Get all transient that start with 'api_cache'
		$transients = $wpdb->get_results(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_api_cache_%'"
		);

		if ( $transients ) {
			foreach ( $transients as $transient ) {
				$key = str_replace( '_transient_', '', $transient->option_name );
				delete_transient( $key );
			}
		}
		
		wp_send_json_success( array( 'message' => 'Successfully synchronized Salesforce data' ) );
	}

	/**
	 * Admin Settings
	 * @param
	 * @return  array
	 * @since 1.0.1
	 */
	public function admin_settings() {

		$settings = array(
			'salesforce_token' => get_option('psei_salesforce_token'),
			'salesforce_instance_url' => get_option('psei_salesforce_instance_url'),
			'salesforce_username' => get_option('psei_salesforce_username'),
			'salesforce_password' => get_option('psei_salesforce_password'),
			'salesforce_client_id' => get_option('psei_salesforce_client_id'),
			'salesforce_client_secret' => get_option('psei_salesforce_client_secret'),
			'salesforce_login_table' => get_option('psei_salesforce_login_table'),
		);

		return $settings;

	}
	
}

new Admin_Settings();