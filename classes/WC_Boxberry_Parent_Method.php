<?php

namespace Boxberry\Woocommerce;

use Exception;
use WC_Payment_Gateways;
use WC_Shipping_Method;
use Boxberry\Client\Client;
use Boxberry\Client\LocationFinder;
use Boxberry\Models\DeliveryCalculation;

class WC_Boxberry_Parent_Method extends WC_Shipping_Method {

	/**
	 * @var mixed
	 */
	protected $key;


	/**
	 * @var mixed
	 */
	protected $from;


	/**
	 * @var mixed
	 */
	protected $addcost;


	/**
	 * @var mixed
	 */
	protected $api_url;


	/**
	 * @var mixed
	 */
	protected $widget_url;


	/**
	 * @var mixed
	 */
	protected $ps_on_status;


	/**
	 * @var bool
	 */
	public bool $self_type;


	/**
	 * @var bool
	 */
	protected bool $payment_after;


	public function __construct( $instance_id = 0 ) {

		parent::__construct();
		$this->instance_id = absint( $instance_id );
		$this->supports    = [
			'shipping-zones',
			'instance-settings',
		];

		$params = [
			'title'                               => [
				'title'   => __( 'Title', 'boxberry' ),
				'type'    => 'text',
				'default' => $this->method_title,
			],
			'key'                                 => [
				'title'             => __( 'Boxberry API Key', 'boxberry' ),
				'type'              => 'text',
				'custom_attributes' => [
					'required' => true,
				],
			],
			'api_url'                             => [
				'title'             => __( 'Boxberry API Url', 'boxberry' ),
				'description'       => '',
				'type'              => 'text',
				'default'           => 'https://api.boxberry.ru/json.php',
				'custom_attributes' => [
					'readonly' => true,
					'required' => true,
				],
			],
			'wiidget_url'                         => [
				'title'             => __( 'Boxberry Widget Url', 'boxberry' ),
				'description'       => '',
				'type'              => 'text',
				'default'           => '//points.boxberry.de/js/boxberry.js',
				'custom_attributes' => [
					'required' => true,
				],
			],
			'default_weight'                      => [
				'title'             => __( 'Default Weight', 'boxberry' ),
				'type'              => 'text',
				'default'           => '500',
				'custom_attributes' => [
					'required' => true,
				],
			],
			'min_weight'                          => [
				'title'             => __( 'Min Weight', 'boxberry' ),
				'type'              => 'text',
				'default'           => '0',
				'custom_attributes' => [
					'required' => true,
				],
			],
			'max_weight'                          => [
				'title'             => __( 'Max Weight', 'boxberry' ),
				'type'              => 'text',
				'default'           => '31000',
				'custom_attributes' => [
					'required' => true,
				],
			],
			'height'                              => [
				'title'   => __( 'Height', 'boxberry' ),
				'type'    => 'text',
				'default' => '',
			],
			'depth'                               => [
				'title'   => __( 'Depth', 'boxberry' ),
				'type'    => 'text',
				'default' => '',
			],
			'width'                               => [
				'title'   => __( 'Width', 'boxberry' ),
				'type'    => 'text',
				'default' => '',
			],
			'parselcreate_on_status'              => [
				'title'    => __( 'ps_on_status_title', 'boxberry' ),
				'desc_tip' => __( 'ps_on_status_desc', 'boxberry' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'default'  => 'none',
				'options'  => [ 'none' => __( 'ps_on_status_none', 'boxberry' ) ] + wc_get_order_statuses(),
			],
			'order_status_send'                   => [
				'title'    => __( 'order_status_send_title', 'boxberry' ),
				'desc_tip' => __( 'order_status_send_desc', 'boxberry' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'default'  => 'none',
				'options'  => [ 'none' => __( 'order_status_send_none', 'boxberry' ) ] + wc_get_order_statuses(),
			],
			'surch'                               => [
				'title'   => __( 'surch', 'boxberry' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => [
					1 => 'Нет',
					0 => 'Да',
				],
			],
			'autoact'                             => [
				'title'   => __( 'autoact', 'boxberry' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => [
					0 => 'Нет',
					1 => 'Да',
				],
			],
			'bxbbutton'                           => [
				'title'   => __( 'bxbbutton', 'boxberry' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => [
					0 => 'Нет',
					1 => 'Да',
				],
			],
			'order_prefix'                        => [
				'title'    => __( 'order_prefix_title', 'boxberry' ),
				'desc_tip' => __( 'order_prefix_desc', 'boxberry' ),
				'type'     => 'text',
				'default'  => 'wp',
			],
			'check_zip'                           => [
				'title'    => __( 'check_zip', 'boxberry' ),
				'desc_tip' => __( 'check_zip_desc', 'boxberry' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => [
					0 => 'Нет',
					1 => 'Да',
				],
			],
			'enable_for_selected_payment_methods' => [
				'title'             => __( 'enable_for_selected_payment_methods', 'boxberry' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 400px;',
				'default'           => '',
				'description'       => __( 'enable_for_selected_payment_methods_desc', 'boxberry' ),
				'options'           => $this->get_available_payment_methods(),
				'desc_tip'          => true,
				'custom_attributes' => [
					'data-placeholder' => __( 'enable_for_selected_payment_methods_data_placeholder', 'boxberry' ),
				],
			],
		];

		if ( is_array( $this->instance_form_fields ) ) {
			$this->instance_form_fields = array_merge( $this->instance_form_fields, $params );
		} else {
			$this->instance_form_fields = $params;
		}

		$this->key          = $this->get_option( 'key' );
		$this->title        = $this->get_option( 'title' );
		$this->from         = $this->get_option( 'from' );
		$this->addcost      = $this->get_option( 'addcost' );
		$this->api_url      = $this->get_option( 'api_url' );
		$this->widget_url   = $this->get_option( 'widget_url' );
		$this->ps_on_status = $this->get_option( 'parselcreate_on_status' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}


	private function is_accessing_settings() {

		if ( is_admin() ) {
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['tab'] ) || 'shipping' !== $_REQUEST['tab'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['instance_id'] ) ) {
				return false;
			}

			return true;
		}

		return false;
	}


	private function get_available_payment_methods() {

		if ( ! $this->is_accessing_settings() ) {
			return [];
		}

		$methods          = [];
		$wc_gateways      = new WC_Payment_Gateways();
		$payment_gateways = $wc_gateways->get_available_payment_gateways();

		foreach ( $payment_gateways as $gateway_id => $gateway ) {
			$methods[ $gateway_id ] = $gateway->get_title();
		}

		return $methods;
	}


	private function check_payment_method_for_calc() {

		$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
		$option                = $this->get_option( 'enable_for_selected_payment_methods' );

		if ( $chosen_payment_method === '' || $option === '' || $chosen_payment_method === $option ) {
			return true;
		}

		if ( is_array( $option ) && in_array( $chosen_payment_method, $option, true ) ) {
			return true;
		}

		return false;
	}


	protected function get_url() {

		return str_replace( [ 'http://', 'https://' ], '', get_site_url() );
	}


	public function calculate_shipping( $package = [] ) {

		if ( ! $this->check_payment_method_for_calc() ) {
			return;
		}

		if ( ( isset( $package['destination']['city'] ) && empty( trim( $package['destination']['city'] ) ) ) || current_action() === 'woocommerce_add_to_cart' ) {

			$this->add_rate(
				[
					'label'   => $this->title,
					'cost'    => 0,
					'taxes'   => false,
					'package' => $package,
				]
			);
		}

		$weight     = 0;
		$dimensions = true;

		$default_height = (int) $this->get_option( 'height' );
		$default_depth  = (int) $this->get_option( 'depth' );
		$default_width  = (int) $this->get_option( 'width' );

		$currentUnit = strtolower( get_option( 'woocommerce_weight_unit' ) );

		$weightC = 1;

		if ( $currentUnit === 'kg' ) {
			$weightC = 1000;
		}

		$dimensionC = $this->get_dimensionC();

		$countProduct   = count( $package['contents'] );
		$currentProduct = null;

		foreach ( $package['contents'] as $cartProduct ) {
			$product = wc_get_product( $cartProduct['product_id'] );

			$itemWeight = Helper::get_weight( $product, $cartProduct['variation_id'] );
			$itemWeight = (float) $itemWeight * $weightC;

			$height = (float) $product->get_height() * $dimensionC;
			$depth  = (float) $product->get_length() * $dimensionC;
			$width  = (float) $product->get_width() * $dimensionC;

			if ( $countProduct === 1 && ( $cartProduct['quantity'] === 1 ) ) {
				$currentProduct = $product;
			}

			$weight += ( ! empty( $itemWeight ) ? $itemWeight
					: (float) $this->get_option( 'default_weight' ) ) * $cartProduct['quantity'];

			$sum_dimensions = $height + $depth + $width;

			if ( $sum_dimensions > 250 ) {
				return;
			}

			if (
				( $default_height > 0 && $height > $default_height )
				|| ( $default_depth > 0 && $depth > $default_depth )
				|| ( $default_width && $width > $default_width )
			) {
				$dimensions = false;
			}
		}

		if (
			(float) $this->get_option( 'min_weight' ) <= $weight
			&& (float) $this->get_option( 'max_weight' ) >= $weight
			&& $dimensions
		) {
			$height = $depth = $width = 0;

			if ( ! is_null( $currentProduct ) ) {
				$height = (int) round( $currentProduct->get_height() * $dimensionC );
				$depth  = (int) round( $currentProduct->get_length() * $dimensionC );
				$width  = (int) round( $currentProduct->get_width() * $dimensionC );
			}

			$client = $this->get_client();

			$location = $this->get_location( $client, $package['destination'] );

			if ( $location->getError() ) {
				return;
			}

			if ( ! $this->is_cod_available_for_country( $location->getCountryCode(), $this->payment_after ) ) {
				return;
			}

			$widget_settings_request = $client->getWidgetSettings();

			try {
				$widget_settings = $client->execute( $widget_settings_request );
			} catch ( Exception $e ) {
				return;
			}

			if ( in_array( $location->getCityCode(), $widget_settings->getCityCode() ) ) {
				return;
			}

			$delivery_calculation = $this->get_calculation( $client, $weight, $height, $width, $depth, $location );

			try {
				$cost_object = $client->execute( $delivery_calculation );
			} catch ( \Exception $e ) {
				return;
			}

			$cost_received = $this->get_received( $cost_object );

			if ( ! $this->self_type && $cost_received <= 0 ) {
				return;
			}

			$delivery_period = $this->get_delivery_period( $cost_object, $widget_settings, $client );

			$this->add_rate( [
				'id'        => $this->get_rate_id(),
				'label'     => $this->title,
				'cost'      => ( ( (float) $this->addcost + (float) $cost_received ) ),
				'meta_data' => [
					'boxberry_delivery_time' => $delivery_period,
				],
			] );
		}
	}


	protected function is_cod_available_for_country( $countryCode, $paymentAfter ): bool {

		if ( $countryCode === '643' || $countryCode === '398' ) {
			return true;
		}

		if ( ! $paymentAfter ) {
			return true;
		}

		return false;
	}


	/**
	 * @return float|int
	 */
	protected function get_dimensionC() {

		$dimensionC    = 1;
		$dimensionUnit = strtolower( get_option( 'woocommerce_dimension_unit' ) );

		switch ( $dimensionUnit ) {
			case 'm':
				$dimensionC = 100;
				break;
			case 'mm':
				$dimensionC = 0.1;
				break;
		}

		return $dimensionC;
	}


	/**
	 * @return \Boxberry\Client\Client
	 */
	protected function get_client(): Client {

		$client = new Client();
		$client->setApiUrl( $this->api_url );
		$client->setKey( $this->key );

		return $client;
	}


	/**
	 * @param  \Boxberry\Client\Client $client
	 * @param                          $destination
	 *
	 * @return \Boxberry\Client\LocationFinder
	 */
	protected function get_location( Client $client, $destination ): LocationFinder {

		$location = new LocationFinder();
		$location->setClient( $client );
		$location->find( $destination['city'], $destination['state'] );

		return $location;
	}


	/**
	 * @param  \Boxberry\Client\Client         $client
	 * @param                                  $weight
	 * @param  int                             $height
	 * @param  int                             $width
	 * @param  int                             $depth
	 * @param  \Boxberry\Client\LocationFinder $location
	 *
	 * @return \Boxberry\Requests\DeliveryCalculationRequest
	 */
	protected function get_calculation( Client $client, $weight, int $height, int $width, int $depth, LocationFinder $location ): \Boxberry\Requests\DeliveryCalculationRequest {

		$total_val = WC()->cart->get_cart_contents_total() + WC()->cart->get_total_tax();
		$surch     = $this->get_option( 'surch' ) !== '' ? (int) $this->get_option( 'surch' ) : 1;

		$deliveryCalculation = $client->getDeliveryCalculation();
		$deliveryCalculation->setWeight( $weight );
		$deliveryCalculation->setHeight( $height );
		$deliveryCalculation->setWidth( $width );
		$deliveryCalculation->setDepth( $depth );
		$deliveryCalculation->setBoxSizes();
		$deliveryCalculation->setRecipientCityId( $location->getCityCode() );
		$deliveryCalculation->setDeliveryType( $this->self_type ? DeliveryCalculation::PICKUP_DELIVERY_TYPE_ID : DeliveryCalculation::COURIER_DELIVERY_TYPE_ID );
		$deliveryCalculation->setPaysum( $this->payment_after ? $total_val : 0 );
		$deliveryCalculation->setOrderSum( $total_val );
		$deliveryCalculation->setUseShopSettings( $surch );
		$deliveryCalculation->setCmsName( 'wordpress' );
		$deliveryCalculation->setVersion( '2.20' );
		$deliveryCalculation->setUrl( $this->get_url() );

		return $deliveryCalculation;
	}


	/**
	 * @param                          $costObject
	 * @param                          $widgetSettings
	 * @param  \Boxberry\Client\Client $client
	 *
	 * @return string
	 */
	protected function get_delivery_period( $costObject, $widgetSettings, Client $client ): string {

		if ( $this->self_type && $costObject->getPriceBasePickup() ) {
			$deliveryPeriod = ! $widgetSettings->getHide_delivery_day() ? $costObject->getDeliveryPeriodPickup() : '';
		} elseif ( ! $this->self_type && $costObject->getPriceBaseCourier() ) {
			$deliveryPeriod = ! $widgetSettings->getHide_delivery_day() ? $costObject->getDeliveryPeriodCourier() : '';
		} else {
			$deliveryPeriod = '';
		}

		if ( $deliveryPeriod ) {
			if ( get_bloginfo( 'language' ) === 'ru-RU' ) {
				$deliveryPeriod = (int) $deliveryPeriod . ' ' . trim(
						$client->setDayForPeriod(
							$deliveryPeriod,
							'рабочий день',
							'рабочих дня',
							'рабочих дней'
						)
					);
			} else {
				$deliveryPeriod = (int) $deliveryPeriod . ' ' . trim(
						$client->setDayForPeriod( $deliveryPeriod, 'day', 'days', 'days' )
					);
			}
		}

		return $deliveryPeriod;
	}


	/**
	 * @param $cost_object
	 *
	 * @return int
	 */
	protected function get_received( $cost_object ): int {

		$is_free_delivery = Helper::is_free_shipping();

		if ( $this->self_type && $cost_object->getPriceBasePickup() ) {
			$cost_received = $cost_object->getTotalPricePickup();
		} elseif ( ! $this->self_type && $cost_object->getPriceBaseCourier() ) {
			$cost_received = $cost_object->getTotalPriceCourier();
		} else {
			$cost_received = 0;
		}

		return $this->self_type && $is_free_delivery ? wc_format_localized_price( 0 ) : $cost_received;
	}
}