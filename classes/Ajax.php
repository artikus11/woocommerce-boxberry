<?php

namespace Boxberry\Woocommerce;

use WC_Cache_Helper;

class Ajax {

	public function init_hooks() {

		add_action( 'wp_ajax_boxberry_update', [ $this, 'checkout_update_points' ] );
		add_action( 'wp_ajax_nopriv_boxberry_update', [ $this, 'checkout_update_points' ] );

		add_action( 'wp_ajax_boxberry_admin_update', [ $this, 'admin_update_points' ] );
	}


	/**
	 * @throws \Exception
	 */
	public function checkout_update_points() {

		$city = ! empty( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : null;
		$cdek_city_id = ! empty( $_POST['cdekCityId'] ) ? (int)sanitize_text_field( $_POST['cdekCityId'] ) : null;

		WC()->session->set( 'bxb_code', sanitize_text_field( $_POST['code'] ) );
		WC()->session->set( 'bxb_address', sanitize_text_field( $_POST['address'] ) );
		WC()->session->set( 'bxb_price', sanitize_text_field( $_POST['price'] ) );

		WC()->customer->set_billing_city( $city );
		WC()->customer->set_shipping_city( $city );
		WC()->customer->save();

		if ( ! empty( $_POST['method'] ) ) {

			WC()->session->set( 'chosen_shipping_methods', [ wc_clean( $_POST['method'] ) ] );
			WC()->cart->calculate_shipping();
			WC()->cart->calculate_totals();
		}

		if ( function_exists( 'wc_edostavka_shipping' ) ) {
			$customer_location = wc_edostavka_shipping()->get_customer_handler();
			$customer_location->set_city_code( $cdek_city_id );
			$customer_location->set_city( $city );

			$customer_location->save();
		}

		//WC_Cache_Helper::get_transient_version( 'shipping', true );

		wp_die();
	}


	public function admin_update_points() {

		if ( empty( $_POST['id'] ) ) {
			wp_die( 'Нет ID заказа' );
		}

		$order = wc_get_order( sanitize_key( $_POST['id'] ) );

		$order->update_meta_data( 'boxberry_code', sanitize_text_field( $_POST['code'] ) );
		$order->update_meta_data( 'boxberry_address', sanitize_text_field( $_POST['address'] ) );

		$order->save();

		wp_die();
	}

}