<?php

namespace Boxberry\Woocommerce;

class Main {

	protected static ?Main $instance = null;


	protected string $suffix;


	protected string $prefix;


	protected string $url;


	protected string $path;


	protected string $url_assets;


	protected string $path_assets;


	/**
	 * Instance.
	 *
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;
	}


	private function __construct() {

		$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min';
		$this->prefix = 'boxberry';

		$this->url_assets  = BOXBERRY_PLUGIN_URI . 'assets/';
		$this->path_assets = BOXBERRY_PLUGIN_PATH . 'assets/';

		$this->init();
		$this->includes();
	}


	private function init() {

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		add_action( 'before_woocommerce_init', [ $this, 'support_hpos' ] );
		add_filter( 'woocommerce_email_classes', [ $this, 'woocommerce_email_classes' ] );
	}


	private function includes() {

		require BOXBERRY_PLUGIN_DIR . '/classes/Boxberry/src/autoload.php';

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Helper.php';

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Shipping_Installer.php';
		( new Shipping_Installer() )->init_hooks();

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Enqueue.php';
		( new Enqueue( $this ) )->init_hooks();

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Ajax.php';
		( new Ajax() )->init_hooks();

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Tracking.php';
		( new Tracking() )->init_hooks();

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Checkout.php';
		( new Checkout() )->init_hooks();

		require_once BOXBERRY_PLUGIN_DIR . '/classes/Order.php';
		( new Order() )->init_hooks();
	}


	public function load_textdomain() {

		load_plugin_textdomain(
			'boxberry',
			false,
			BOXBERRY_PLUGIN_DIR . '/language'
		);
	}

	public function woocommerce_email_classes( $emails ) {

		if ( ! isset( $emails['Tracking_Email'] ) ) {
			$emails['Tracking_Email'] = require_once BOXBERRY_PLUGIN_DIR . '/classes/Tracking_Email.php';;
		}

		return $emails;
	}
	/**
	 * @return string
	 */
	public function get_prefix(): string {

		return $this->prefix;
	}


	/**
	 * @return string
	 */
	public function get_suffix(): string {

		return $this->suffix;
	}


	/**
	 * @return string
	 */
	public function get_url_assets(): string {

		return $this->url_assets;
	}


	/**
	 * @return string
	 */
	public function get_path_assets(): string {

		return $this->path_assets;
	}


	public function support_hpos() {

		if ( ! class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			BOXBERRY_PLUGIN_AFILE
		);
	}
}