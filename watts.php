<?php
/**
 * Plugin Name:     Watts
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     EFO for Contact Fomr 7.
 * Author:          s.matsuura
 * Author URI:      https://tonica.llc
 * Text Domain:     watts
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Watts
 */

include "modules/dob.php";
require_once 'includes/controller.php';

add_action('wp_enqueue_scripts', 'watts_enqueue_scripts');
function watts_enqueue_scripts()
{
	wp_enqueue_script('watts-zenkaku-to-hankaku', plugin_dir_url(__FILE__) . 'includes/js/zenkaku-to-hankaku.js', array('contact-form-7'));
	wp_enqueue_script('watts-auto-validation', plugin_dir_url(__FILE__) . 'includes/js/auto-validation.js', array('contact-form-7'));
}

add_action('rest_api_init', function () {
	$controller = new Watts_Rest_Controller;
	$controller->register_routes();
});
