<?php

namespace Boxberry\Woocommerce;

use Boxberry\Client\Client;
use Exception;
use WC_Shipping_Zones;

class Checkout {

	public function init_hooks() {


		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_order_review' ], 10, 1 );

		add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_checkout' ], 10, 2 );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'update_choice_point' ] );

		add_action( 'woocommerce_review_order_after_shipping', [ __CLASS__, 'add_button_choice_point' ] );

		add_filter( 'woocommerce_package_rates', [ $this, 'delivery_rates' ], 10, 2 );
	}


	public function delivery_rates( $rates, $package ) {

		if ( ! $_POST || is_admin() || ! is_ajax() ) {
			return $rates;
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		$bxb_price = WC()->session->get( 'bxb_price' );

		if ( empty( $chosen_methods ) ) {
			return $rates;
		}

		$is_free_delivery = Helper::is_free_shipping();

		if ( ! $bxb_price || $is_free_delivery ) {
			return $rates;
		}

		if ( empty( $rates[ $chosen_methods[0] ] ) ) {
			return $rates;
		}

		if ( false === strpos( $chosen_methods[0], 'boxberry_self' ) ) {
			return $rates;
		}

		if ( $rates[ $chosen_methods[0] ]->get_cost() !== $bxb_price ) {
			return $rates;
		}

		$rates[ $chosen_methods[0] ]->set_cost( $bxb_price );

		return $rates;
	}


	public static function add_button_choice_point() {

		$chosen_rate = self::get_chosen_rate_shipping();

		if ( is_checkout() && $chosen_rate ) {

			$shipping = WC_Shipping_Zones::get_shipping_method( $chosen_rate );

			if ( isset( $shipping ) ) {

				$widget_key = self::get_widget_key( $shipping );

				$city = self::get_city();

				[ $weight, $height, $depth, $width ] = self::get_dimension_and_weight( $shipping );

				$total_val = WC()->cart->get_cart_contents_total() + WC()->cart->get_total_tax();

				$surch = $shipping->get_option( 'surch' ) !== '' ? (int) $shipping->get_option( 'surch' ) : 1;

				$package = WC()->shipping()->get_packages();

				$chosen_shipping_method = WC()->session->get( 'chosen_shipping_methods' );

				if ( false !== strpos( $chosen_shipping_method[0], 'boxberry_self_after' ) ) {
					$payment = $total_val;
				} else {
					$payment = 0;
				}

				$pvzimg = '<img src=\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAYCAYAAAD6S912AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAE+SURBVHgBnVSBccMgDNR5Am9QRsgIjMIGZYN4g2QDpxN0BEagGzgbpBtQqSdiBQuC/Xc6G0m8XogDQEFKaUTzaAHtkVZEtBnNQi8w+bMgof+FTYKIzTuyS7HBKsqdIKfvqUZ2fpv0mj+JDkwZdILMQCcEaSwDuQULO8GDI7hS3VzZYFmJ09RzfFWJP981deJcU+tIhMoPWtDdSo3KJYKSe81tD7imid63zYIFHZr/h79mgDp+K/47NDBwgkG5YxG7VTZ/KT7zLIZEt8ZQjDhwusBeIZNDOcnDD3AAXPT/BkjnUlPZQTjnCUunO6KSWtyoE8HAQb+DcNmoU6ptXw+dLD91cyvJc1JUrpHM63+dROuXStyk9UW30NHKKM7mrJDl2AS9KFR4USiy7wp7kV5fm4coEOEomDQI0qk1LMIfknqE+j7lxtgAAAAASUVORK5CYII=\'>';

				$link_with_img = $shipping->get_option( 'bxbbutton' ) ? $pvzimg : '';
				$classes       = 'button wc-boxberry-choose-delivery-point';

				$out = __return_empty_string();

				$button_label = 'Выбрать пункт выдачи';
				if ( ! empty( $package[0]['destination']['city'] ) ) {
					if ( $chosen_delivery_point = WC()->session->get( 'bxb_address' ) ) {
						$out .= sprintf( '<p>%s: <strong>%s</strong></p>',
							'Выбранный ПВЗ',
							$chosen_delivery_point
						);

						$button_label = 'Выбрать другой пункт выдачи';
					}

					$out .= sprintf(
						'<button type="button" id="%s" class="%s" data-surch ="%s" data-boxberry-open="true" data-method="%s" data-boxberry-token="%s" data-boxberry-city="%s" data-boxberry-weight="%s" data-paymentsum="%s" data-ordersum="%s" data-height="%s" data-width="%s" data-depth="%s" data-api-url="%s">%s</button>',
						esc_attr( str_replace( ':', '-', $chosen_shipping_method[0] ) ),
						esc_attr( $classes ),
						esc_attr( $surch ),
						esc_attr( $chosen_shipping_method[0] ),
						esc_attr( $widget_key ),
						esc_attr( $city ),
						esc_attr( $weight ),
						esc_attr( $payment ),
						esc_attr( $total_val ),
						esc_attr( $height ),
						esc_attr( $width ),
						esc_attr( $depth ),
						esc_attr( $shipping->get_option( 'api_url' ) ),
						$button_label
					);

					$out = sprintf(
						'<div class="cart-delivery-points-wrapper"><h4>%s</h4><div class="cart-delivery-points-out">%s</div></div>',
						'Пункт выдачи Boxberry',
						$out
					);

					echo $out;
				}
			}
		}
	}


	public function update_choice_point( $order ) {

		$bxb_code    = WC()->session->get( 'bxb_code' );
		$bxb_address = WC()->session->get( 'bxb_address' );

		if ( $bxb_address && $bxb_code ) {
			$order->update_meta_data( 'boxberry_code', $bxb_code );
			$order->update_meta_data( 'boxberry_address', $bxb_address );
		}
	}


	public function validate_checkout( $data, $errors ) {

		if ( ! empty( $errors->get_error_message( 'shipping' ) ) ) {
			return;
		}

		$shipping_method = array_map( static function ( $i ) {

			$i = explode( ':', $i );

			return $i[0];
		}, (array) $data['shipping_method'] );

		$chosen_delivery_point = WC()->session->get( 'bxb_code' );

		if (
			( ( ! $data['ship_to_different_address'] && ! $data['billing_city'] )
			  || ( $data['ship_to_different_address'] && ! $data['shipping_city'] ) )
			&& ( strpos( $shipping_method[0], 'boxberry' ) !== false )
		) {
			$errors->add( 'shipping', '<strong>Необходимо указать город для расчета доставки Boxberry</strong>' );
		} elseif ( empty( $chosen_delivery_point ) && strpos( $shipping_method[0], 'boxberry_self' ) !== false ) {
			$errors->add( 'shipping', '<strong>Необходимо выбрать пункт выдачи Boxberry</strong>' );
		}
	}


	public function update_order_review( $post_data ) {

		$bool = true;
		if ( WC()->session->get( 'bxb_code' ) ) {
			$bool = false;
		}

		foreach ( WC()->cart->get_shipping_packages() as $package_key => $package ) {
			WC()->session->set( 'shipping_for_package_' . $package_key, $bool );
		}

		WC()->cart->calculate_shipping();
	}


	/**
	 * @param $shipping
	 *
	 * @return string
	 */
	protected static function get_widget_key( $shipping ): string {

		$key = $shipping->get_option( 'key' );

		$client = new Client();
		$client->setApiUrl( $shipping->get_option( 'api_url' ) );
		$client->setKey( $key );
		$widgetKeyMethod = $client::getKeyIntegration();
		$widgetKeyMethod->setToken( $key );

		try {
			$widgetResponse = $client->execute( $widgetKeyMethod );

			if ( empty( $widgetResponse ) ) {
				return '';
			}
		} catch ( Exception $ex ) {
			return '';
		}

		return $widgetResponse->getWidgetKey();
	}


	/**
	 * @param $shipping
	 *
	 * @return float[]|int[]
	 */
	protected static function get_dimension_and_weight( $shipping ): array {

		$weight       = 0;
		$current_unit = strtolower( get_option( 'woocommerce_weight_unit' ) );
		$weight_c     = 1;

		if ( $current_unit === 'kg' ) {
			$weight_c = 1000;
		}

		$dimension_c    = 1;
		$dimension_unit = strtolower( get_option( 'woocommerce_dimension_unit' ) );

		switch ( $dimension_unit ) {
			case 'm':
				$dimension_c = 100;
				break;
			case 'mm':
				$dimension_c = 0.1;
				break;
		}

		$cartProducts = WC()->cart->get_cart();
		$countProduct = count( $cartProducts );

		$height = 0;
		$depth  = 0;
		$width  = 0;

		foreach ( $cartProducts as $cartProduct ) {
			$product = wc_get_product( $cartProduct['product_id'] );

			$itemWeight = Helper::get_weight( $product, $cartProduct['variation_id'] );
			$itemWeight = (float) $itemWeight * $weight_c;

			if ( $countProduct == 1 && ( $cartProduct['quantity'] == 1 ) ) {
				$height = (float) $product->get_height() * $dimension_c;
				$depth  = (float) $product->get_length() * $dimension_c;
				$width  = (float) $product->get_width() * $dimension_c;
			}

			$weight += ( ! empty( $itemWeight ) ? $itemWeight : (float) $shipping->get_option( 'default_weight' ) ) * $cartProduct['quantity'];
		}

		return [ $weight, $height, $depth, $width ];
	}


	/**
	 * @return array|false|string|string[]|null
	 */
	protected static function get_city() {

		$billing_city  = WC()->customer->get_billing_city();
		$shipping_city = WC()->customer->get_shipping_city();

		if ( ! empty( $shipping_city ) ) {
			$city = $shipping_city;
		} elseif ( ! empty( $billing_city ) ) {
			$city = $billing_city;
		} else {
			$city = '';
		}

		return str_replace( [ 'Ё', 'Г ', 'АЛМАТЫ' ], [ 'Е', '', 'АЛМА-АТА' ], mb_strtoupper( $city ) );
	}


	public static function get_chosen_rate_shipping(): int {

		if ( WC()->session ) {
			$shipping_packages               = WC()->shipping()->get_packages();
			$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			if ( ! empty( $chosen_shipping_methods_session ) && is_array( $chosen_shipping_methods_session ) ) {
				foreach ( $chosen_shipping_methods_session as $package_key => $chosen_package_rate_id ) {
					if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
						$chosen_rate = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];

						if ( false !== strpos( $chosen_rate->get_method_id(), 'boxberry_self' ) ) {
							return $chosen_rate->get_instance_id();
						}
					}
				}
			}
		}

		return 0;
	}
}