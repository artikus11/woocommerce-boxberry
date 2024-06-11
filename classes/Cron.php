<?php

namespace Boxberry\Woocommerce;

use Exception;

class Cron {

	/**
	 * @var int
	 */
	private int $orders_update_interval;


	public function __construct() {

		add_filter( 'cron_schedules', [ $this, 'add_cron_interval' ] );

		add_action( 'wp', [ $this, 'add_cron_event' ] );

		add_action( 'woocommerce_boxberry_orders_update', [ $this, 'update_all_orders' ] );

		$this->orders_update_interval = 6 * HOUR_IN_SECONDS;
	}


	public function add_cron_interval( $schedules ): array {

		if ( $this->orders_update_interval ) {
			$schedules['woocommerce_boxberry_orders'] = [
				'interval' => $this->orders_update_interval,
				'display'  => 'Каждые 6 часов',
			];
		}

		return $schedules;
	}


	public function add_cron_event() {

		if ( ! wp_next_scheduled( 'woocommerce_boxberry_orders_update' ) ) {
			wp_schedule_event(
				time() + $this->orders_update_interval,
				'woocommerce_boxberry_orders',
				'woocommerce_boxberry_orders_update'
			);
		}
	}


	/**
	 * @throws \Boxberry\Client\Exceptions\BadSettingsException
	 * @throws \Boxberry\Requests\Exceptions\RequiredFieldsNullException
	 * @throws \Boxberry\Client\Exceptions\UnknownTypeException
	 * @throws \Boxberry\Client\Exceptions\BadResponseException
	 */
	public function update_all_orders() {

		$orders = wc_get_orders( [
				'status'     => [ 'wc-processing' ],
				'limit'      => 200,
				'meta_query' => [
					[
						'key'     => 'boxberry_tracking_number',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		foreach ( $orders as $order ) {

			$shipping_instance = Helper::get_shipping_instance( $order );

			if ( empty( $shipping_instance ) ) {
				continue;
			}

			$tracking_number = $order->get_meta( 'boxberry_tracking_number' );

			if ( empty( $tracking_number ) ) {
				continue;
			}

			$client = ( new Tracking() )->get_client( $shipping_instance );

			$listStatuses = $client->getListStatuses();
			$listStatuses->setImId( $tracking_number );

			try {
				$answer = $client->execute( $listStatuses );

				if ( $answer->valid() ) {
					$offset = $answer->count() - 1;

					if ( $answer->offsetGet( $offset ) !== null ) {
						if ( 'Выдано' === $answer->offsetGet( $offset )->getName() ) {
							$order->update_status( 'completed' );
						}
					}
				}
			} catch ( Exception $e ) {

			}
		}
	}
}