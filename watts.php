<?php
/**
 * Plugin Name:     Watts
 * Plugin URI:      https://wp-watts.com/
 * Description:     EFO for Contact Form 7.
 * Author:          s.matsuura
 * Author URI:      https://github.com/solaito/
 * Text Domain:     watts
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Watts
 */

require_once 'includes/controller.php';

if (get_locale() === 'ja')
{
	require_once "modules/dob.php";
}

add_action('wp_enqueue_scripts', 'watts_enqueue_scripts');
function watts_enqueue_scripts()
{
	$data = get_file_data(__FILE__, array('version' => 'Version'));
	$version = $data['version'];
	wp_enqueue_script('watts-zenkaku-to-hankaku', plugin_dir_url(__FILE__) . 'includes/js/zenkaku-to-hankaku.js', array('contact-form-7'), $version);
	if(!(strstr($_SERVER['HTTP_USER_AGENT'], 'Trident') || strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')))
	{
		wp_enqueue_script('watts-auto-validation', plugin_dir_url(__FILE__) . 'includes/js/auto-validation.js', array('contact-form-7'), $version);
	}
}

add_action('rest_api_init', function () {
	$controller = new Watts_Rest_Controller;
	$controller->register_routes();
});
