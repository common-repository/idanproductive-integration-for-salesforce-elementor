<?php
/**
 * Plugin Name: Elemetix - Integration for Salesforce and Elementor
 * Description: An elementor addon for interaction between wordpress and salesforce.
 * Version:     1.0.4
 * Author:      Productive
 * Author URI:  https://productive.co.il/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: elemetix
 * 
 * Requires Plugins: elementor
 * Elementor tested up to: 3.23.3
 * Elementor Pro tested up to: 3.23.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly..
}

if ( ! function_exists( 'ele_fs' ) ) {
    // Create a helper function for easy SDK access.
    function ele_fs() {
        global $ele_fs;

        if ( ! isset( $ele_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $ele_fs = fs_dynamic_init( array(
                'id'                  => '16304',
                'slug'                => 'elemetix',
                'type'                => 'plugin',
                'public_key'          => 'pk_6e0626f580cd100ca82b01044c638',
                'is_premium'          => true,
                'is_premium_only'     => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'trial'               => array(
                    'days'               => 14,
                    'is_require_payment' => false,
                ),
                'menu'                => array(
                    'slug'           => 'elemetix',
                    'support'        => false,
                ),
            ) );
        }

        return $ele_fs;
    }

    // Init Freemius.
    ele_fs();
    // Signal that SDK was initiated.
    do_action( 'ele_fs_loaded' );
}

// Define constants
define( 'PSEI_PATH', plugin_dir_path( __FILE__ ) );
define( 'PSEI_ASSETS_URL', plugins_url( '/', __FILE__ ) . 'assets/' );
define( 'PSEI_ADMIN', PSEI_PATH . 'admin/' );	
define( 'PSEI_ADMIN_URL', plugins_url( '/', __FILE__ ) . 'admin/' );	
define( 'PSEI_PLUGIN_VERSION', '1.0.4' );
define( 'PSEI_INCLUDES_PATH', PSEI_PATH . 'includes/' );


/**
 * Remot POST request with caching
 * @param string $url The URL to send the request to
 * @param array $args The arguments for the request
 * @return array|WP_Error The response or WP_Error on failure
 */
function wp_remote_post_with_cache( $url, $args = array() ) {
	$cache_key = 'api_cache_' . md5( $url . serialize( $args ) );
	$cached_data = get_transient( $cache_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$response = wp_remote_post( $url, $args );
	if ( is_wp_error( $response ) ) {
		error_log( 'Error in wp_remote_post: ' . $response->get_error_message() );
		return $response;
	}

	set_transient( $cache_key, $response, HOUR_IN_SECONDS );

	return $response;
}

/**
 * Remote GET request with catching
 * @param string $url The URL to send the request to.
 * @param array $args The arguments for the request.
 * @return array|WP_Error The response or WP_Error on failure.
 */
function wp_remote_get_with_cache( $url, $args = array() ) {
	$cache_key = 'api_cache_' . md5( $url . serialize( $args ) );
	$cached_data = get_transient( $cache_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$response = wp_remote_get( $url, $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	set_transient( $cache_key, $response, HOUR_IN_SECONDS );

	return $response;
}

function PSEI_init() {

	// Load plugin file
	require_once( __DIR__ . '/includes/plugin.php' );

	// Run the plugin
	\Productive_Salesforce_Elementor_Integration\Plugin::instance();

}
add_action( 'plugins_loaded', 'PSEI_init' );