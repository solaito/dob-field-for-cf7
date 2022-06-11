<?php
/**
 * Plugin Name:     Watts
 * Plugin URI:      https://wp-watts.com/
 * Description:     The Watts will performs EFO(Entry Form Optimization) for your Contact Form 7.
 * Author:          Tonica, LLC.
 * Author URI:      https://github.com/solaito/
 * Text Domain:     watts
 * Version:         1.2.1
 *
 * @package         Watts
 */

define( "WATTS_PLUGIN", __FILE__ );
define( "WATTS_PLUGIN_BASENAME", plugin_basename( WATTS_PLUGIN ) );
define( "WATTS_PLUGIN_DIR_URL", plugin_dir_url( WATTS_PLUGIN ) );

require_once 'includes/options-page.php';
require_once 'includes/controller.php';

require_once "modules/dob.php";
require_once "modules/confirm-email.php";

add_action( 'wp_enqueue_scripts', 'watts_enqueue_scripts' );
function watts_enqueue_scripts() {
	$data    = get_file_data( WATTS_PLUGIN, array( 'version' => 'Version' ) );
	$version = $data['version'];
	$options = get_option( 'watts_option_name' );
	if ( $options[ Watts_Options_Page::IDS['FULL_TO_HALF_ENABLE'] ] === 'on' ) {
		wp_enqueue_script( 'watts-full-to-half', WATTS_PLUGIN_DIR_URL . 'includes/js/full-to-half.js', array( 'contact-form-7' ), $version );
	}
	if ( $options[ Watts_Options_Page::IDS['REALTIME_VALIDATION_ENABLE'] ] === 'on' ) {
		if ( ! ( strstr( $_SERVER['HTTP_USER_AGENT'], 'Trident' ) || strstr( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) ) ) {
			wp_enqueue_script( 'watts-realtime-validation', WATTS_PLUGIN_DIR_URL . 'includes/js/realtime-validation.js', array( 'contact-form-7' ), $version );
			$watts = array(
				'api'    => array(
					'root'      => esc_url_raw( get_rest_url() ),
					'namespace' => 'watts/v1',
				),
				'plugin' => array(
					'dir'                  => esc_url_raw( WATTS_PLUGIN_DIR_URL ),
					'validate_icon_enable' => $options[ Watts_Options_Page::IDS['VALIDATE_ICON_ENABLE'] ] === 'on',
					'validate_icon_size'   => isset( $options[ Watts_Options_Page::IDS['VALIDATE_ICON_SIZE'] ] ) ? $options[ Watts_Options_Page::IDS['VALIDATE_ICON_SIZE'] ] : 'medium',
				)
			);
			wp_localize_script( 'watts-realtime-validation', 'watts', $watts );
		}
	}
	wp_enqueue_style( 'watts', WATTS_PLUGIN_DIR_URL . 'includes/css/style.css', array( 'contact-form-7' ), $version );
}

add_action( 'rest_api_init', function () {
	$controller = new Watts_Rest_Controller;
	$controller->register_routes();
} );
