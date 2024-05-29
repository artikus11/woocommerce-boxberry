<?php

namespace Boxberry\Woocommerce;

class Enqueue {

	protected Main $main;


	/**
	 * @var string[]
	 */
	protected array $depends;


	public function __construct( $main ) {

		$this->main = $main;
		$this->depends = [
			'jquery',
			$this->main->get_prefix() . '_points',
		];
	}


	public function init_hooks() {

		add_action( 'wp_enqueue_scripts', [ $this, 'style' ], 900 );

		/**
		 * На хуке wp_print_scripts потому как на хуке wp_enqueue_scripts плагин сдека подключает
		 * через слишком большой приоритет установленный через PHP_INT_MAX
		 */
		add_action( 'wp_print_scripts', [ $this, 'script' ]  );


		add_action( 'admin_enqueue_scripts', [ $this, 'script_admin' ] , 900 );
	}


	public function style() {

		if ( is_cart() || is_checkout() ) {
			wp_enqueue_style( $this->main->get_prefix() . '_button',
				$this->main->get_url_assets() . 'css/bxbbutton.css',
				[],
				filemtime( $this->main->get_path_assets() . 'css/bxbbutton.css' )
			);
		}
	}


	public function script() {

		if ( is_cart() || is_checkout() ) {
			$this->enqueue_points();

			wp_enqueue_script( $this->main->get_prefix() . '_script_handle',
				$this->main->get_url_assets() . 'js/boxberry.js',
				$this->depends,
				filemtime( $this->main->get_path_assets() . 'js/boxberry.js' ),
				[
					'in_footer' => true,
					'strategy'  => 'async',
				]
			);

			wp_localize_script(
				$this->main->get_prefix() . '_script_handle',
				$this->main->get_prefix() . '_handle',
				[
					'ajax_url' => admin_url('admin-ajax.php')
				]
			);

		}
	}

	public function script_admin() {
			$this->enqueue_points();

			wp_enqueue_script( $this->main->get_prefix() . '_script_handle',
				$this->main->get_url_assets() . 'js/boxberry_admin.js',
				$this->depends,
				filemtime( $this->main->get_path_assets() . 'js/boxberry_admin.js' ),
				[
					'in_footer' => true,
					'strategy'  => 'async',
				]
			);
	}


	/**
	 * @return void
	 */
	protected function enqueue_points(): void {

		$widget_url = get_option( 'wiidget_url' ) ? : 'https://points.boxberry.de/js/boxberry.js';

		wp_enqueue_script( $this->main->get_prefix() . '_points',
			$widget_url,
			[ 'jquery' ],
			BOXBERRY_PLUGIN_VER,
			[
				'in_footer' => true,
				'strategy'  => 'async',
			]
		);
	}

}