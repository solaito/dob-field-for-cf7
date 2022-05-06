<?php
/**
 * Plugin Name:     Watts
 * Plugin URI:      https://wp-watts.com/
 * Description:     The Watts will performs EFO(Entry Form Optimization) for your Contact Form 7.
 * Author:          Tonica, LLC.
 * Author URI:      https://github.com/solaito/
 * Text Domain:     watts
 * Domain Path:     /languages
 * Version:         1.2.0
 *
 * @package         Watts
 */

require_once 'includes/controller.php';

require_once "modules/dob.php";
require_once "modules/confirm-email.php";

add_action('wp_enqueue_scripts', 'watts_enqueue_scripts');
function watts_enqueue_scripts()
{
	$data = get_file_data(__FILE__, array('version' => 'Version'));
	$version = $data['version'];
	if (in_array(get_locale(), ['ja', 'ko_KR', 'zh_CN', 'zh_HK', 'zh_TW'], true)) {
		wp_enqueue_script('watts-zenkaku-to-hankaku', plugin_dir_url(__FILE__) . 'includes/js/zenkaku-to-hankaku.js', array('contact-form-7'), $version);
	}
	if(!(strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') || strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))) {
		wp_enqueue_script('watts-auto-validation', plugin_dir_url(__FILE__) . 'includes/js/auto-validation.js', array('contact-form-7'), $version);
		$watts= array(
			'api' => array(
					'root' => esc_url_raw( get_rest_url() ),
					'namespace' => 'watts/v1',
			),
			'plugin' => array(
					'dir' => esc_url_raw(plugin_dir_url(__FILE__)),
			)
		);
		wp_localize_script( 'watts-auto-validation', 'watts', $watts );
	}
	wp_enqueue_style('watts', plugin_dir_url(__FILE__) . 'includes/css/style.css', array('contact-form-7'), $version);
}

add_action('rest_api_init', function () {
	$controller = new Watts_Rest_Controller;
	$controller->register_routes();
});
