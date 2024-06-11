<?php

namespace Boxberry\Woocommerce;

use Boxberry\Client\Client;
use Boxberry\Client\LocationFinder;
use Boxberry\Client\ParselCreateResponse;
use Boxberry\Collections\Items;
use Boxberry\Models\CourierDelivery;
use Boxberry\Models\CourierDeliveryExport;
use Boxberry\Models\Customer;
use Boxberry\Models\Item;
use Boxberry\Models\Parsel;
use Exception;
use WC_Emails;
use WC_Order;

class Tracking {

	public function __construct() {}


	public function init_hooks() {

		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ], 1, 2 );

		add_action( 'woocommerce_process_shop_order_meta', [ $this, 'meta_tracking_code' ], 0, 1 );

		add_action( 'woocommerce_order_status_changed', [ $this, 'register_on_status' ], 10, 3 );
	}


	public function add_meta_box( $post_type, $post ) {

		$order = wc_get_order( $post );

		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$shipping_method_id = Helper::get_shipping_method_id( $order );
		$shipping_title     = Helper::get_shipping_title( $order );

		if ( false === strpos( $shipping_method_id, 'boxberry' ) ) {
			return;
		}

		add_meta_box(
			'boxberry_meta_tracking_code',
			__( $shipping_title, 'boxberry' ),
			[ $this, 'tracking_code' ],
			wc_get_page_screen_id( 'shop-order' ),
			'side',
			'high'
		);
	}


	public function register_on_status( $order_id, $previous_status, $next_status ) {

		$order         = wc_get_order( $order_id );
		$shipping_data = Helper::get_shipping_data( $order );

		if (
			isset( $shipping_data['method_id'], $shipping_data['object'] )
			&& strpos( $shipping_data['method_id'], 'boxberry' ) !== false
		) {

			$parsel_create_status = $shipping_data['object']->get_option( 'parselcreate_on_status' );

			if (
				$next_status === substr( $parsel_create_status, 3 )
				&& ! $order->get_meta( 'boxberry_tracking_number' )
			) {
				$this->get_tracking_code( $order_id );
			}
		}
	}


	public function meta_tracking_code( $post_id ) {

		if ( isset( $_POST['boxberry_create_parsel'] ) ) {
			$this->get_tracking_code( $post_id );
		}

		if ( isset( $_POST['boxberry_create_act'] ) ) {
			$this->create_act( $post_id );
		}
	}


	protected function get_tracking_code( $post_id ) {

		$order = wc_get_order( $post_id );

		$shipping_instance  = Helper::get_shipping_instance( $order );
		$shipping_method_id = Helper::get_shipping_method_id( $order );
		$shipping_cost      = Helper::get_shipping_cost( $order );

		if ( empty( $shipping_instance ) ) {
			return;
		}

		$client = $this->get_client( $shipping_instance );

		$parsel_create = $client::getParselCreate();

		$parsel = new Parsel();

		$parsel->setSourcePlatform( 'wordpress' );
		$parsel->setOrderId( ( $shipping_instance->get_option( 'order_prefix' )
				? $shipping_instance->get_option( 'order_prefix' ) . '_'
				: '' ) . $order->get_order_number() );

		$parsel->setPrice( apply_filters( 'bxbw_price', $order->get_total() - $shipping_cost ) );

		$parsel->setDeliverySum( $shipping_cost );

		if ( ! str_contains( $shipping_method_id, '_after' ) ) {
			$parsel->setPaymentSum( 0 );
		} else {
			$parsel->setPaymentSum( $order->get_total() );
		}

		$customer = $this->get_customer( $order );

		$parsel->setCustomer( $customer );

		$items = $this->get_items( $order, $shipping_instance );

		$parsel->setItems( $items );

		$shop = [
			'name'  => '',
			'name1' => '',
		];

		if ( str_contains( $shipping_method_id, 'boxberry_self' ) ) {
			$parsel->setVid( 1 );
			$boxberry_code = $order->get_meta( 'boxberry_code' );

			if ( $boxberry_code === '' ) {
				$this->set_error( $order, 'Для доставки до пункта ПВЗ нужно указать его код' );

				return;
			}

			$shop['name']  = $boxberry_code;
			$shop['name1'] = $shipping_instance->get_option( 'from' );
		} else {

			[ $post_code, $shipping_city, $shipping_state, $shipping_address ] = $this->get_post_shipping_data( $order );

			$location = new LocationFinder();
			$location->setClient( $client );
			$location->find( $shipping_city, $shipping_state );

			if ( $location->getError() ) {
				$this->set_error( $order, $location->getError() );

				return;
			}

			$this->set_export_no_russia( $location, $client, $parsel, $order, $shipping_city, $shipping_address );

			$parsel->setVid( 2 );

			$courierDost = new CourierDelivery();
			$courierDost->setIndex( $post_code );
			$courierDost->setCity( $shipping_city );
			$courierDost->setAddressp( $shipping_address );

			$parsel->setCourierDelivery( $courierDost );
		}

		$parsel->setShop( $shop );

		$parsel_create->setParsel( $parsel );

		$auto_act    = (int) $shipping_instance->get_option( 'autoact' );
		$auto_status = $shipping_instance->get_option( 'order_status_send' );

		try {
			/** @var ParselCreateResponse $answer */
			$answer = $client->execute( $parsel_create );

			if ( $answer->getTrack() !== '' ) {

				$order->update_meta_data( 'boxberry_tracking_number', $answer->getTrack() );
				$order->update_meta_data( 'boxberry_link', $answer->getLabel() );
				$order->save();

				if ( $auto_act === 1 ) {
					$this->create_act( $post_id );
				}

				if ( $auto_status && wc_is_order_status( $auto_status ) ) {
					$order->update_status( $auto_status, sprintf( __( 'Успешная регистрация в Boxberry: %s ', 'boxberry' ), $answer->getTrack() ) );
				}

				$this->set_emails( $order, $answer, $auto_status );
			}
		} catch ( Exception $e ) {
			if ( $e->getMessage() === 'Ваша учетная запись заблокирована' ) {
				$error_message = sprintf( 'В профиле доставки <b>"%s"</b> указан не верный API-token, либо данный профиль доставки удален. Проверить ваш API-token вы можете <a href="https://account.boxberry.ru/client/infoblock/index?tab=api&api=methods" target="_blank">здесь</a>. Если API-token указан корректно и ошибка повторяется обратитесь в <a href="https://sd.boxberry.ru" target="_blank">техподдержку</a>',
					$shipping_instance->get_option( 'title' )
				);
				$this->set_error( $order, $error_message );
			} else {
				$this->set_error( $order, $e->getMessage() );
			}
		}
	}


	public function create_act( $post_id ) {

		$order = wc_get_order( $post_id );

		$shipping_instance = Helper::get_shipping_instance( $order );

		if ( empty( $shipping_instance ) ) {
			return;
		}

		$tracking_number = $order->get_meta( 'boxberry_tracking_number' );

		$key     = $shipping_instance->get_option( 'key' );
		$api_url = $shipping_instance->get_option( 'api_url' );

		$parsel_send_request = wp_remote_get( sprintf( '%s?token=%s&method=ParselSend&ImIds=%s', $api_url, $key, $tracking_number ) );
		$parsel_send         = json_decode( wp_remote_retrieve_body( $parsel_send_request ), true );

		if ( ! empty( $parsel_send['label'] ) ) {
			$order->update_meta_data( 'boxberry_act_link', $parsel_send['label'] );
			$order->update_meta_data( 'boxberry_tracking_site_link', 'https://boxberry.ru/tracking-page?id=' . $tracking_number );
			$order->save();
		}

		if ( ! empty( $parsel_send['err'] ) ) {
			$order->update_meta_data( 'boxberry_error', $parsel_send['err'] );
			$order->save();
		}
	}


	public function tracking_code( $post ) {

		$order = wc_get_order( $post );

		$shipping_instance = Helper::get_shipping_instance( $order );

		if ( empty( $shipping_instance ) ) {
			return;
		}

		$tracking_number    = $order->get_meta( 'boxberry_tracking_number' );
		$tracking_site_link = $order->get_meta( 'boxberry_tracking_site_link' );
		$label_link         = $order->get_meta( 'boxberry_link' );
		$act_link           = $order->get_meta( 'boxberry_act_link' );
		$error_text         = $order->get_meta( 'boxberry_error' );
		$pvz_code           = $order->get_meta( 'boxberry_code' );
		$boxberry_address   = $order->get_meta( 'boxberry_address' );

		$client = $this->get_client( $shipping_instance );

		$order_data = [
			'track'  => $tracking_number,
			'act'    => $act_link,
			'client' => $client,
			'order'  => $order,
		];

		if ( ! empty( $error_text ) && empty( $tracking_number ) ) {
			echo '<p><b><u>Возникла ошибка</u></b>: ' . $error_text . '</p>';

			echo '<p><input type="submit" class="add_note button" name="boxberry_create_parsel" value="Попробовать снова"></p>';

			if ( $shipping_instance->self_type ) {
				echo $this->get_pvz_link( $order, $pvz_code );
				echo $this->get_pvz_address( $boxberry_address, $pvz_code );
			}
		} elseif ( isset( $tracking_number ) && $tracking_number !== '' ) {
			echo '<p><span style="display: inline-block;">Номер отправления:</span>';
			echo '<span style="margin-left: 10px"><b>' . esc_html( $tracking_number ) . '</b></span>';

			if ( ! empty( $tracking_site_link ) ) {
				echo sprintf( '<p><a class="button" href="%s" target="_blank">Посмотреть на сайте Boxberry</a></p>',
					esc_url( $tracking_site_link )
				);
			}

			echo sprintf( '<p><a class="button" href="%s" target="_blank">Скачать этикетку</a></p>',
				esc_url( $label_link )
			);

			if ( ! empty( $act_link ) ) {
				echo sprintf( '<p><a class="button" href="%s" target="_blank">Скачать акт</a></p>', esc_url( $act_link ) );
			} else {
				echo '<p><input type="submit" class="add_note button" name="boxberry_create_act" value="Сформировать акт"></p>';
			}

			echo '<p>Текущий статус заказа в Boxberry:</p>';
			echo $this->get_last_status_in_order( $order_data );
		} else {
			if ( $shipping_instance->self_type ) {
				if ( ! empty( $pvz_code ) ) {
					echo $this->get_pvz_link( $order, $pvz_code );
					echo $this->get_pvz_address( $boxberry_address, $pvz_code );
				} else {
					echo $this->get_pvz_link( $order, $pvz_code );
				}
			}

			echo '<p>После нажатия кнопки заказ будет создан в системе Boxberry.</p>';
			echo '<p><input type="submit" class="add_note button" name="boxberry_create_parsel" value="Отправить заказ в систему"></p>';
		}
	}


	protected function get_last_status_in_order( $data ): string {

		$listStatuses = $data['client']->getListStatuses();
		$listStatuses->setImId( $data['track'] );

		try {
			$answer = $data['client']->execute( $listStatuses );

			if ( $answer->valid() ) {
				$offset = $answer->count() - 1;

				if ( $answer->offsetGet( $offset ) !== null ) {
					if ( 'Выдано' === $answer->offsetGet( $offset )->getName() ) {
						$data['order']->update_status( 'completed' );
					}

					return sprintf( '<div><ul class="order_notes"><li class="note system-note"><div class="note_content"><p>%s</p></div><p class="meta"><abbr class="exact-date">%s</abbr></p></li></ul></div>',
						esc_html( $answer->offsetGet( $offset )->getName() ),
						esc_html( $answer->offsetGet( $offset )->getDate() )
					);
				}
			}
		} catch ( Exception $e ) {
			return '<div>
                        <ul class="order_notes">
                            <li class="note">
                                <div class="note_content">
                                    <p>На данный момент статусы по заказу еще не доступны.</p>
                                </div>
                            </li>
                        </ul>
                   </div>';
		}

		return '';
	}


	/**
	 * @param $order
	 * @param $pvz_code
	 *
	 * @return string
	 */
	protected function get_pvz_link( $order, $pvz_code ): string {

		return sprintf( '<p>%s<button type="button" class="%s" data-id="%s" data-boxberry-open="true" data-boxberry-city="%s">%s</button></p>',
			$pvz_code ? 'Код пункта выдачи: ' : '',
			$pvz_code ? 'button-link' : 'button-secondary',
			esc_attr( $order->get_id() ),
			$pvz_code ? esc_attr( $order->get_shipping_city() ) : esc_attr( $order->get_shipping_state() ) . ' ' . esc_attr( $order->get_shipping_city() ),
			$pvz_code ? esc_attr( $pvz_code ) : 'Выберите ПВЗ'
		);
	}


	/**
	 * @param $boxberry_address
	 * @param $pvz_code
	 *
	 * @return string
	 */
	protected function get_pvz_address( $boxberry_address, $pvz_code ): string {

		if ( empty( $pvz_code ) ) {
			return '';
		}

		return sprintf( '<p>Адрес пункта выдачи: <br><span>%s</span></p>', esc_html( $boxberry_address ) );
	}


	/**
	 * @param  object $shipping_instance
	 *
	 * @return \Boxberry\Client\Client
	 */
	public function get_client( object $shipping_instance ): Client {

		$client = new Client();
		$client->setApiUrl( $shipping_instance->get_option( 'api_url' ) );
		$client->setKey( $shipping_instance->get_option( 'key' ) );

		return $client;
	}


	/**
	 * @param $order
	 *
	 * @return \Boxberry\Models\Customer
	 */
	protected function get_customer( $order ): Customer {

		$customer_name = $order->get_formatted_shipping_full_name();

		if ( trim( $customer_name ) === '' ) {
			$customer_name = $order->get_formatted_billing_full_name();
		}

		$address = sprintf( '%s, %s, %s, %s',
			$order->get_shipping_state(),
			$order->get_shipping_city(),
			$order->get_shipping_address_1(),
			$order->get_shipping_address_2()
		);

		if ( trim( str_replace( ',', '', $address ) ) === '' ) {
			$address = sprintf( '%s, %s, %s, %s',
				$order->get_billing_state(),
				$order->get_billing_city(),
				$order->get_billing_address_1(),
				$order->get_billing_address_2()
			);
		}

		$customer_phone = $order->get_meta( '_shipping_phone' );

		if ( trim( $customer_phone ) === '' ) {
			$customer_phone = $order->get_billing_phone();
		}

		$customer_email = $order->get_meta( '_shipping_email' );

		if ( trim( $customer_email ) === '' ) {
			$customer_email = $order->get_billing_email();
		}

		$customer = new Customer();
		$customer->setFio( $customer_name );
		$customer->setEmail( $customer_email );
		$customer->setPhone( $customer_phone );

		return $customer;
	}


	/**
	 * @param         $order
	 * @param  object $shipping_instance
	 *
	 * @return \Boxberry\Collections\Items
	 */
	protected function get_items( $order, object $shipping_instance ): Items {

		$items       = new Items();
		$order_items = $order->get_items();

		foreach ( $order_items as $key => $order_item ) {
			$current_unit = strtolower( get_option( 'woocommerce_weight_unit' ) );
			$weight_c     = 1;

			if ( $current_unit === 'kg' ) {
				$weight_c = 1000;
			}

			$product = wc_get_product( $order_item['product_id'] );

			$item_weight = Helper::get_weight( $product, $order_item['variation_id'] );
			$item_weight = (int) ( $item_weight * $weight_c * $order_item["qty"] );

			if ( $item_weight === 0 ) {
				$item_weight = $shipping_instance->get_option( 'default_weight' ) * $order_item['qty'];
			}

			$item = new Item();
			$id   = (string) ( ( ! empty( $product->get_sku() ) ) ? $product->get_sku() : $order_item['product_id'] );

			$item_price  = apply_filters( 'bxbw_item_price', (float) $order_item['total'] / $order_item['qty'] );
			$item_weight = apply_filters( 'bxbw_item_weight', $item_weight );

			$item->setId( $id );
			$item->setName( $order_item['name'] );
			$item->setPrice( $item_price );
			$item->setQuantity( $order_item['qty'] );
			$item->setWeight( $item_weight );

			$items[] = $item;

			unset( $product );
		}

		return $items;
	}


	/**
	 * @param $order
	 * @param $err
	 *
	 * @return void
	 */
	protected function set_error( $order, $err ): void {

		$order->update_meta_data( 'boxberry_error', $err );
		$order->save();
	}


	/**
	 * @param $order
	 *
	 * @return array
	 */
	protected function get_post_shipping_data( $order ): array {

		$post_code = $order->get_shipping_postcode();
		if ( is_null( $post_code ) || trim( (string) $post_code ) === '' ) {
			$post_code = $order->get_billing_postcode();
		}

		$shipping_city = $order->get_shipping_city();
		if ( is_null( $shipping_city ) || trim( (string) $shipping_city ) === '' ) {
			$shipping_city = $order->get_billing_city();
		}
		$shipping_state = $order->get_shipping_state();
		if ( is_null( $shipping_state ) || trim( (string) $shipping_state ) === '' ) {
			$shipping_state = $order->get_billing_state();
		}
		$shipping_address = $order->get_shipping_address_1() . ', ' . $order->get_shipping_address_2();
		if ( trim( str_replace( ',', '', $shipping_address ) ) === '' ) {
			$shipping_address = $order->get_billing_address_1() . ', ' . $order->get_billing_address_2();
		}

		return [ $post_code, $shipping_city, $shipping_state, $shipping_address ];
	}


	/**
	 * @param  \Boxberry\Client\LocationFinder $location
	 * @param  \Boxberry\Client\Client         $client
	 * @param  \Boxberry\Models\Parsel         $parsel
	 * @param                                  $order
	 * @param                                  $shipping_city
	 * @param                                  $shipping_address
	 *
	 * @return void
	 */
	protected function set_export_no_russia( LocationFinder $location, Client $client, Parsel $parsel, $order, $shipping_city, $shipping_address ) {

		if ( $location->getCountryCode() !== '643' ) {
			try {
				$dadataSuggestions = $client->getDadataSuggestions();
				$dadataSuggestions->setQuery( $shipping_city . ' ' . $shipping_address );
				$dadataSuggestions->setLocations();
				$dadataSuggestions->fixCityName();
				$dadataRequestResult = $client->execute( $dadataSuggestions );
			} catch ( Exception $e ) {
				$error = 'Не удалось определить город, попробуйте отредактировать адрес и выгрузить заказ повторно, либо создать заказ вручную в ЛК.';
				$this->set_error( $order, $error );

				return;
			}

			try {
				$export = new CourierDeliveryExport();
				$export->setIndex( $export::EAEU_COURIER_DEFAULT_INDEX );
				$export->setCountryCode( $location->getCountryCode() );
				$export->setCityCode( $location->getCityCode() );
				$export->setArea( $dadataRequestResult->getArea() );
				$export->setStreet( $dadataRequestResult->getStreet() );
				$export->setHouse( $dadataRequestResult->getHouse() );
				$export->setFlat( $dadataRequestResult->getFlat() );
				$export->setTransporterGuid( $export::TRANSPORTER_GUID );

				$parsel->setExport( $export );
			} catch ( Exception $e ) {
				$this->set_error( $order, $e->getMessage() );

				return;
			}

			$client->disableDebugMode();
		}
	}


	/**
	 * @param  WC_Order                              $order
	 * @param  \Boxberry\Client\ParselCreateResponse $answer
	 * @param                                        $auto_status
	 *
	 * @return void
	 */
	protected function set_emails( WC_Order $order, ParselCreateResponse $answer, $auto_status ): void {

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( empty( $order->get_meta( 'boxberry_tracking_number' ) ) ) {
			return;
		}

		WC_Emails::instance();

		do_action( 'woocommerce_boxberry_tracking_code', $order, $answer->getTrack(), $auto_status );
	}

}