<?php

namespace Boxberry\Woocommerce;

use WC_Shipping_Zones;

class Helper {

	public static function get_weight( $product, $id = 0 ) {

		if ( $product->is_type( 'simple' ) ) {
			return (float) $product->get_weight();
		}

		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_visible_children() as $variationId ) {
				$variation = wc_get_product( $variationId );
				if ( $id === $variation->get_id() ) {
					return (float) $variation->get_weight();
				}
			}
		}

		return 0;
	}


	public static function get_shipping_data( $order ): array {

		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );

		$method_id          = $shipping_method->get_method_id();
		$method_instance_id = $shipping_method->get_instance_id();
		$total              = $shipping_method->get_total();

		$method_instance = WC_Shipping_Zones::get_shipping_method( $method_instance_id );

		if ( $method_instance ) {
			return [
				'method_id'       => $method_id,
				'object'          => $method_instance,
				'cost'            => $total,
				'title'           => $method_instance->get_option( 'title' ),
				'shipping_method' => $shipping_method,
			];
		}

		return [];
	}


	/**
	 * @param $order
	 *
	 * @return object
	 */
	public static function get_shipping_method( $order ): ?object {

		$shipping_data = self::get_shipping_data( $order );

		return ! empty( $shipping_data['shipping_method'] ) && is_object( $shipping_data['shipping_method'] ) ? $shipping_data['shipping_method'] : null;
	}


	/**
	 * @param $order
	 *
	 * @return object
	 */
	public static function get_shipping_instance( $order ): object {

		$shipping_data = self::get_shipping_data( $order );

		return $shipping_data['object'];
	}


	/**
	 * @param $order
	 *
	 * @return string
	 */
	public static function get_shipping_method_id( $order ): string {

		$shipping_data = self::get_shipping_data( $order );

		return ! empty( $shipping_data['method_id'] ) ? (string) $shipping_data['method_id'] : '';
	}


	/**
	 * @param $order
	 *
	 * @return int
	 */
	public static function get_shipping_cost( $order ): int {

		$shipping_data = self::get_shipping_data( $order );

		return (int) $shipping_data['cost'];
	}


	/**
	 * @param $order
	 *
	 * @return string
	 */
	public static function get_shipping_title( $order ): string {

		$shipping_data = self::get_shipping_data( $order );

		return ! empty( $shipping_data['title'] ) ? (string) $shipping_data['title'] : '';
	}


	public static function is_free_shipping(): bool {

		$contents_cost = WC()->cart->get_subtotal();
		$free          = 14999;

		$is_free_delivery = false;
		if ( $contents_cost > $free ) {
			$is_free_delivery = true;
		}

		return $is_free_delivery;
	}

}