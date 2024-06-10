<?php
/**
 * Plugin Name:  WooCommerce Boxberry
 * Description: The plugin allows you to automatically calculate the shipping cost and create Parsel for Boxberry. Fork by Artem Abramovich
 * Version: 1.0.1
 * Author: Artem Abramovich
 * Author URI: Boxberry.ru
 * Text Domain: boxberry
 * Domain Path: /language
 *
 * WC requires at least: 8.0.0
 * WC tested up to: 8.0
 *
 * Requires PHP: 7.4
 * Requires WP: 5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_data = get_file_data(
	__FILE__,
	[
		'ver'  => 'Version',
		'name' => 'Plugin Name',
	]
);

const BOXBERRY_PLUGIN_DIR   = __DIR__;
const BOXBERRY_PLUGIN_AFILE = __FILE__;

define( 'BOXBERRY_PLUGIN_URI', plugin_dir_url( BOXBERRY_PLUGIN_AFILE ) );
define( 'BOXBERRY_PLUGIN_PATH', plugin_dir_path( BOXBERRY_PLUGIN_AFILE ) );
define( 'BOXBERRY_PLUGIN_VER', $plugin_data['ver'] );
define( 'BOXBERRY_PLUGIN_NAME', $plugin_data['name'] );

require BOXBERRY_PLUGIN_DIR . '/classes/Main.php';

if ( ! function_exists( 'bxw' ) ) {
	function bxw() {

		return Boxberry\Woocommerce\Main::instance();
	}
}

bxw();
