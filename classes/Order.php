<?php

namespace Boxberry\Woocommerce;

class Order {

	public function init_hooks() {

		add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hidden_order_meta' ] );
		add_action( 'woocommerce_admin_order_items_after_shipping', [ $this, 'view_order_delivery_time' ], 5, 1 );
	}


	public function hidden_order_meta( $meta ): array {

		return array_merge( $meta, [ 'boxberry_delivery_time' ] );
	}


	public function view_order_delivery_time( $order_id ): void {

		$order = wc_get_order( $order_id );

		if ( ! $order->get_shipping_method() ) {
			return;
		}

		$shipping_method = Helper::get_shipping_method( $order );
		$delivery_time   = ! is_null( $shipping_method ) ? $shipping_method->get_meta( 'boxberry_delivery_time' ) : '';

		if ( ! $delivery_time ) {
			return;
		}

		?>
		<tr class="packages" data-order_order_id="<?php echo esc_attr( $order_id ); ?>">
			<td class="data" colspan="2">
				<?php
				echo wp_kses_post( sprintf(
					'<div class="shipping_method--delivery-time"><strong>Срок доставки</strong>: %s</div>',
					$delivery_time,
				) );
				?>
			</td>
		</tr>
		<?php
	}
}