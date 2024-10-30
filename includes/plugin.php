<?php 
namespace Productive_Salesforce_Elementor_Integration;
session_start();

require_once( PSEI_INCLUDES_PATH . 'salesforce.php' );

use Productive_Salesforce_Elementor_Integration\Salesforce;

/**
 * Plugin class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * Addon Version
	 *
	 * @since 1.0.0
	 * @var string The addon version.
	 */
	const VERSION = '1.0.3';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 * @var string Minimum Elementor version required to run the addon.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.5.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 * @var string Minimum PHP version required to run the addon.
	 */
	const MINIMUM_PHP_VERSION = '7.3';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var \Productive_Salesforce_Elementor_Integration\Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
     * Salesforce instance
     *
     * @var Salesforce
     */
    private $salesforce;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return \Productive_Salesforce_Elementor_Integration\Plugin An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
                           
	}
	

	/**
	 * Constructor
	 *
	 * Perform some compatibility checks to make sure basic requirements are meet.
	 * If all compatibility checks pass, initialize the functionality.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
			$this->includes();           
		}

	}

	public function includes() {
		if( is_admin() ) {
            include_once PSEI_PATH . 'admin/dashboard-settings.php';
        }
	}

	/**
	 * Compatibility Checks
	 *
	 * Checks whether the site meets the addon requirement.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function is_compatible() {
		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elemetix' ),
			'<strong>' . esc_html__( 'Productive Salesforce Elementor Integration', 'elemetix' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elemetix' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', esc_html($message) );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elemetix' ),
			'<strong>' . esc_html__( 'Productive Salesforce Elementor Integration', 'elemetix' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'elemetix' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', esc_html($message) );

	}	

	/**
	 * Initialize
	 *
	 * Load the addons functionality only after Elementor is initialized.
	 *
	 * Fired by `elementor/init` action hook.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		// Include widget files
		$this->include_widget_files();

		add_action( 'elementor/elements/categories_registered', [$this, 'register_categories'] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_ajax_submit_login', [$this, 'psei_submit_login']);
		add_action( 'wp_ajax_nopriv_submit_login', [$this, 'psei_submit_login']);
		add_action( 'wp_ajax_submit_otp', [$this, 'psei_submit_otp']);
		add_action( 'wp_ajax_nopriv_submit_otp', [$this, 'psei_submit_otp']);
		add_action('wp_enqueue_scripts', [ $this, 'psei_enqueue_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [$this, 'psei_editor_scripts'] );
		add_action( 'elementor/editor/after_enqueue_styles', [$this, 'psei_styles'] );
		add_action( 'elementor/frontend/after_enqueue_styles', [$this, 'psei_styles'] );
		add_action( 'wp_ajax_update_profile', [$this, 'psei_update_profile'] );
		add_action( 'wp_ajax_nopriv_update_profile', [$this, 'psei_update_profile'] );
		add_action( 'elementor/widget/render_content', [$this, 'psei_save_otp_type'], 10, 2 );
		add_action( 'wp_ajax_send_otp', [$this, 'psei_send_otp'] );
		add_action( 'wp_ajax_nopriv_send_otp', [$this, 'psei_send_otp'] );

		// Table fields
		add_action('wp_ajax_psei_get_table_fields', [$this, 'psei_get_table_fields_ajax']);
		add_action('wp_ajax_nopriv_psei_get_table_fields', [$this, 'psei_get_table_fields_ajax']);
		add_action('wp_ajax_psei_fetch_preview_data', [$this, 'fetch_preview_data']);
	}    
	
	/**
	 *  Get table Fields
	 * 
	 * @since 1.0.4
	 * 
	 */
	public function psei_get_table_fields_ajax() {
		check_ajax_referer( 'psei_ajax_nonce', 'nonce' );
		
		if ( ! isset( $_POST[ 'table' ] ) || ! isset( $_POST[ 'widget' ] ) ) {
			wp_send_json_error( 'Table or widget not specified' );
			return;
		}
		
		$table = sanitize_text_field( $_POST[ 'table' ] );
		$widget_name = sanitize_text_field( $_POST[ 'widget' ] );
		
		// Get Elementor's widget manager
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		
		// Try to get the widget instance
		$widget = $widgets_manager->get_widget_types( $widget_name );
		
		if ( ! $widget ) {
			wp_send_json_error( 'Invalid widget specified: ' . $widget_name );
			return;
		}
		
		// Check if the widget has the get_table_fields method
		if ( ! method_exists( $widget, 'get_table_fields' ) ) {
			wp_send_json_error( 'Widget does not support fetching table fields' );
			return;
		}
		
		// Initialize Salesforce if not already done
		if ( ! isset( $this->salesforce ) ) {
			$this->PSEIInitializeSalesforce();
		}
		
		try {
			$fields = $widget->get_table_fields( $table );
			
			if ( empty( $fields ) ) {
				wp_send_json_error( 'No fields found for the specified table' );
				return;
			}
			
			wp_send_json_success( $fields );
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error fetching fields: ' . $e->getMessage() );
		}
	}	

	public function fetch_preview_data() {
		check_ajax_referer( 'psei_ajax_nonce', 'nonce' );
	
		$table = sanitize_text_field( $_POST[ 'table' ] );
		$fields = isset( $_POST[ 'fields' ] ) ? array_map( 'sanitize_text_field', $_POST[ 'fields' ] ) : [];
		$limit = isset( $_POST[ 'limit'] ) ? intval( $_POST[ 'limit' ] ) : 5;
	
		$this->PSEIInitializeSalesforce();
		$results = $this->salesforce->PSEIFetchObjectRecords( $table, $fields, $limit );
	
		wp_send_json_success( $results[ 'data' ] );
	}        


	// Initialize Salesforce here
	public function PSEIInitializeSalesforce() {

		// Get saved Salesforce credentials
		$saved_salesforce_token = get_option( 'psei_salesforce_token' );
		$saved_salesforce_token = get_option( 'psei_salesforce_token' );
		$saved_salesforce_username = get_option( 'psei_salesforce_username' );
		$saved_salesforce_password = get_option( 'psei_salesforce_password' );
		$saved_salesforce_client_id = get_option( 'psei_salesforce_client_id' );
		$saved_salesforce_client_secret = get_option( 'psei_salesforce_client_secret' );
		$saved_salesforce_instance_url = get_option( 'psei_salesforce_instance_url' );
		$saved_salesforce_access_token = get_option( 'psei_salesforce_access_token' );
		
		$this->salesforce = Salesforce::instance(
			$saved_salesforce_client_id,
			$saved_salesforce_client_secret,
			$saved_salesforce_username,
			$saved_salesforce_password,
			$saved_salesforce_token,
			$saved_salesforce_instance_url,
			$saved_salesforce_access_token
		);
  	}


	// Get table options
	public function PSEIGetTableOptions() {
		$results = $this->salesforce->PSEIGetObjects();
		$fieldOptions = [];
		if ( $results[ 'error' ]) {
		  $this->error = $results[ 'error_description' ] . '. Tables could not be fetched.';
		} else {
		  $res = $results[ 'data' ];
		  $response = $res->sobjects;
		  $sObjects = array_column( $response, 'name' );
		  foreach ( $sObjects as $object ) {
			$fieldOptions[ $object ] = $object;
		  }
		}
		return $fieldOptions;
	}

	// Get table fieldsRemeWe need 
	public function PSEIGetTableFieldsFromSalesforce( $table ) {
		$this->PSEIInitializeSalesforce();
		$results = $this->salesforce->PSEIFetchObjectFields( $table );
		$fieldOptions = [];
		if ( $results[ 'error' ]) {
			$this->error = $results[ 'error' ];
			} else {
			  foreach ( $results[ 'data' ] as $field ) {
				$fieldOptions[ $field->name ] = $field->label;
			  }

			  $salesforce_data = get_option( 'psei_salesforce_data', [] );
			  $salesforce_data[ 'table_fields' ][ $table ]  = $fieldOptions;
			  update_option('psei_salesforce_data', $salesforce_data);
			}
			
		return $fieldOptions;		
	}


	private function include_widget_files() {
		require_once(__DIR__ . '/widgets/table-view-widget.php');
		require_once(__DIR__ . '/widgets/login-widget.php');
		require_once(__DIR__ . '/widgets/otp-widget.php');
		require_once(__DIR__ . '/widgets/profile-page-widget.php');
		require_once(__DIR__ . '/widgets/sidebar-widget.php');
		require_once(__DIR__ . '/widgets/orders-card-widget.php');
		require_once(__DIR__ . '/widgets/fields-widget.php');
		require_once( __DIR__ . '/widgets/reports-widget.php' );
	}

	/**
	 * Register Widgets
	 *
	 * Load widgets files and register new Elementor widgets.
	 *
	 * Fired by `elementor/widgets/register` action hook.
	 *
	 * @param \Elementor\Widgets_Manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		$widgets_manager->register( new \PSEI_Table_View_Widget() );
		$widgets_manager->register( new \PSEI_Login_Widget() );
		$widgets_manager->register( new \PSEI_OTP_Widget() );
		$widgets_manager->register( new \PSEI_Profile_Page_Widget() );
		$widgets_manager->register( new \PSEI_Sidebar_Widget() );
		$widgets_manager->register( new \PSEI_Orders_Card_Widget() );
		$widgets_manager->register( new \PSEI_Fields_Widget() );
		$widgets_manager->register( new \PSEI_Reports_Widget() );

	}
                                             

	function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'esei-salesforce',
			[
				'title' => esc_html__( 'ESEI Salesforce', 'elemetix' ),
				'icon' => 'fa fa-plug',
			]
		);
	}

	function psei_submit_login() {
		check_ajax_referer( 'psei_login_nonce_action', 'security' );

		if( isset( $_POST['fields'] ) ) {
			parse_str( sanitize_text_field($_POST['fields']), $login );
		} else {
			return;
		}

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
        $result = $salesforce->PSEILogin($login['phone_number']);
        if($result['data']){
			if(count($result['data']) > 0){
				$data = $result['data'][0];
				$id = $data->Id;
				$_SESSION['psei_salesforce_user_id'] = $id;
				$res = [
					'status' => 'success',
					'message' => 'Login successful',
					'data' => [
						'redirect_url' => get_page_link($login['next_page']),
						'message' => 'Login successful',
					]
				];
				return wp_send_json($res);
			} else {
				$res = [
					'status' => 'error',
					'message' => $login['error_message'],
					'data' => [
						'redirect_url' => '',
						'message' => 'User not found',
					]
				];
				return wp_send_json($res);
			}
			
            
        } else {
            $res = [
                'status' => 'error',
                'message' => 'Account with this phone number not found',
                'data' => [
                    'redirect_url' => '',
                    'message' => $result['error_description']
                ]
            ];
            return wp_send_json($res);
        }
	}              

	function psei_submit_otp(){
		check_ajax_referer( 'psei_otp_nonce_action', 'security' );   

		if( isset( $_POST['fields'] ) ) {
			$otp_fields = $_POST['fields'];
			if (is_array($otp_fields)) {
				foreach ($otp_fields as &$field) {
					$field = esc_attr($field);
				}
				unset($field );
			} else {
				$otp_fields = esc_attr($otp_fields);
			}


		} else {
			$res = [
				'status' => 'error',
				'message' => 'Enter OTP',
				'data' => [
					'redirect_url' => '',
					'message' => 'OTP code is required'
				]
			];
			return wp_send_json($res);
		}

		error_log("==== OTP CODE === " . json_encode($otp_fields) . "\n", 3, ABSPATH . 'error_log');
		if($otp_fields['otp_code'] == '123456'){
			$res = [
				'status' => 'success',
				'message' => 'OTP verified',
				'data' => [
					'redirect_url' => get_page_link($otp_fields['next_page']),
					'message' => 'OTP verified'
				]
			];
			return wp_send_json($res);
			
		} else {
			$res = [
				'status' => 'error',
				'message' => 'OTP verification failed',
				'data' => [
					'redirect_url' => '',
					'message' => 'OTP code is incorrect'
				]
			];
			return wp_send_json($res);
		}
		
		
	}

	/**
	 * PSEI Enqueue Scripts
	 *
	 * Enqueue plugin scripts and styles for both frontend and backend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 *
	 */
	function psei_enqueue_scripts() {
		wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js', [], '4.4.4', true );
		

		// Localize Orders Card Script
		wp_localize_script( 'psei-orders-card-widget', 'psei_ajax_object', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'psei_ajax_nonce' )
		));
	}

	/**
	 * PSEI Editor Scripts
	 *
	 * Enqueue plugin scripts for Elementor editor.
	 *
	 * Fired by `elementor/editor/before_enqueue_scripts` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 *
	 */
	function psei_editor_scripts() {
		wp_register_script( 'editor-script', PSEI_ASSETS_URL . 'js/psei-admin-script.js', array( 'jquery' ), PSEI_PLUGIN_VERSION, true );
		wp_enqueue_script( 'chartjs', ' https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js', [], '4.4.4', true );
		wp_enqueue_script(' psei-table-view-widget', PSEI_ASSETS_URL . 'js/psei-table-view-script.js', array( 'jquery' ), PSEI_PLUGIN_VERSION, true );
		wp_enqueue_script(' psei-fields-widget', PSEI_ASSETS_URL . 'js/psei-fields-widget.js', array( 'jquery' ), PSEI_PLUGIN_VERSION, true );
	
		$localized_data = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('psei_ajax_nonce')
		);
	
		wp_localize_script('psei-table-view-widget', 'psei_ajax_object', $localized_data);
		wp_localize_script('psei-fields-widget', 'psei_ajax_object', $localized_data);
	
		wp_enqueue_script('editor-script');
	}

	function psei_styles() {
		wp_register_style( 'bootstrap-icons', PSEI_ASSETS_URL . 'css/bootstrap-icons.css' );
		wp_register_style( 'psei-style', PSEI_ASSETS_URL . 'css/psei-style.css', array(), PSEI_PLUGIN_VERSION, 'all' );
		wp_enqueue_style( 'psei-style' );
		wp_enqueue_style( 'bootstrap-icons' );
	}

	function psei_update_profile() {
		check_ajax_referer( 'psei_update_profile_nonce_action', 'security' );

		if( isset( $_POST['fields'] ) ) {
			parse_str( sanitize_text_field($_POST['fields']), $profile );
		} else {
			return;
		}

		foreach($profile as $key=>$value){
				if($profile[$key] == $profile["original-$key"]){
					unset($profile[$key]);
					unset($profile["original-$key"]);
				} else {
					unset($profile["original-$key"]);
				}
		}

		error_log("==== PROFILE STRING === " . json_encode($profile) . "\n", 3, ABSPATH . 'error_log');

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
		$result = $salesforce->updateProfile(sanitize_text_field($_SESSION['psei_salesforce_user_id']), $profile);
		if($result['data']){
			$data = $result['data'][0];
			$id = $data->Id;
			$_SESSION['psei_salesforce_user_id'] = $id;
			$res = [
				'status' => 'success',
				'message' => 'Profile updated successfully',
				'data' => [
					'message' => 'Profile updated successfully'
				]
			];
			return wp_send_json($res);
			
		} else {
			$res = [
				'status' => 'error',
				'message' => 'Profile update failed',
				'data' => [
					'message' => $result['error_description']
				]
			];
			return wp_send_json($res);
		}
	}

	function psei_save_otp_type( $widget_content, $widget ) {

		if($widget->get_name() == 'otp'){
			$settings = $widget->get_settings();
			$otp_type = $settings['otp_send_type'];
			update_option('psei_otp_type', $otp_type);
		}
		

		return $widget_content;
	}

	function psei_send_otp() {
		
	}

}


add_action( 'elementor_pro/forms/actions/register', function ( $form_actions_registrar ) {
	require_once( __DIR__ . '/form-actions/elementor-form-salesforce-action.php' );

	$form_actions_registrar->register( new \PSEI_Form_Action_Elementor() );
} );


add_action( 'elementor/controls/register', function ( $controls_manager ) {

	require_once( __DIR__ . '/controls/salesforce-form-mapping-control.php' );

    $controls_manager->register( new \PSEI_Form_Mapping_Control() );
});




// add_action('wp_ajax_custom_ajax_request', 'custom_ajax_request'); 
add_action('wp_ajax_custom_ajax_request', function () {
	// Check for the presence of an AJAX nonce for security
	check_ajax_referer('custom_ajax_nonce', 'security');

	 // Now you can access the data as an object
	 $access_token = sanitize_text_field($_POST['access_token']);
	 $sObject = sanitize_text_field($_POST['sObject']);
	 $relationship = sanitize_text_field($_POST['relationship']);
	 $record_id = sanitize_text_field($_POST['record_id']);

	
	// Your Salesforce API call logic here
	// Make sure to sanitize and validate your input

	$saved_salesforce_instance_url = get_option('psei_salesforce_instance_url');

	$url = "$saved_salesforce_instance_url/services/data/v58.0/sobjects/$sObject/$record_id/$relationship";

	$response = wp_safe_remote_get($url, array(
			'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
			),
	));

	if (is_wp_error($response)) {
			echo esc_html('Error: ' . $response->get_error_message());
	} else {
			$data = wp_remote_retrieve_body($response);
			echo esc_html($data);
	}

	wp_die(); // Always include this to terminate the AJAX request
}); // For non-logged-in users


// Generate the nonce and store it in a variable
$custom_ajax_nonce = wp_create_nonce('custom_ajax_request');