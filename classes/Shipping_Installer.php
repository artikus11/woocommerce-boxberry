<?php

namespace Boxberry\Woocommerce;

class Shipping_Installer {

	public function init_hooks() {

		add_action( 'woocommerce_shipping_init', [ $this, 'include_woocommerce_shipping_method' ] );
		add_filter( 'woocommerce_shipping_methods', [ $this, 'add_woocommerce_shipping_method' ] );
	}


	public function include_woocommerce_shipping_method() {

		require_once BOXBERRY_PLUGIN_DIR . '/classes/WC_Boxberry_Parent_Method.php';
		require_once BOXBERRY_PLUGIN_DIR . '/classes/WC_Boxberry_Courier_Method.php';
		require_once BOXBERRY_PLUGIN_DIR . '/classes/WC_Boxberry_CourierAfter_Method.php';
		require_once BOXBERRY_PLUGIN_DIR . '/classes/WC_Boxberry_Self_Method.php';
		require_once BOXBERRY_PLUGIN_DIR . '/classes/WC_Boxberry_SelfAfter_Method.php';
	}


	public function add_woocommerce_shipping_method( $methods ) {

		$methods['boxberry_self']          = 'Boxberry\Woocommerce\WC_Boxberry_Self_Method';
		$methods['boxberry_courier']       = 'Boxberry\Woocommerce\WC_Boxberry_Courier_Method';
		$methods['boxberry_self_after']    = 'Boxberry\Woocommerce\WC_Boxberry_SelfAfter_Method';
		$methods['boxberry_courier_after'] = 'Boxberry\Woocommerce\WC_Boxberry_CourierAfter_Method';

		return $methods;
	}
}