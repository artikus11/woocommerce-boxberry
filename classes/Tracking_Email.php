<?php

namespace Boxberry\Woocommerce;

use WC_Email;
use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tracking_Email', false ) ) :

	class Tracking_Email extends WC_Email {

		protected bool $has_preview;


		public function __construct() {

			$this->id             = 'boxberry_self_tracking';
			$this->customer_email = true;
			$this->has_preview    = true;
			$this->title          = 'Boxberry - отправка трекинга';
			$this->description    = 'Письмо с номером отслеживания отправляется покупателю когда заказ был успешно экспортирован в Boxberry.';

			$this->template_base  = BOXBERRY_PLUGIN_DIR . '/templates';
			$this->template_html  = '/emails/tracking-code.php';
			$this->template_plain = '/emails/plain/tracking-code.php';

			$this->placeholders = [
				'{order_date}'    => '',
				'{order_number}'  => '',
				'{tracking_code}' => '',
			];

			parent::__construct();

			add_action( 'woocommerce_boxberry_tracking_code', [ $this, 'trigger' ] );
			add_action( 'admin_init', [ $this, 'preview_emails' ] );
			add_action( 'woocommerce_email_setting_column_preview', [ $this, 'add_preview_email_action' ] );
		}


		public function trigger( WC_Order $order = null ) {

			if ( method_exists( $this, 'setup_locale' ) ) {
				$this->setup_locale();
			}

			if ( is_a( $order, 'WC_Order' ) ) {


				if ( $this->customer_email && ! $this->recipient ) {
					$this->recipient = $order->get_billing_email();
				}

				$this->object                          = $order;
				$this->recipient                       = $this->object->get_billing_email();
				$this->placeholders['{order_date}']    = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}']  = $this->object->get_order_number();
				$this->placeholders['{tracking_code}'] = $this->object->get_meta( 'boxberry_tracking_number' );
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send(
					$this->get_recipient(),
					$this->get_subject(),
					$this->get_content(),
					$this->get_headers(),
					$this->get_attachments()
				);

				$this->object->update_meta_data( 'boxberry_tracking_number_email_send', true );
			}

			if ( method_exists( $this, 'restore_locale' ) ) {
				$this->restore_locale();
			}
		}


		public function get_content_args( $type = 'html' ) {

			return [
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'message_text'       => $this->format_string( $this->get_option( 'message_text', $this->get_default_message_text() ) ),
				'sent_to_admin'      => ! $this->customer_email,
				'plain_text'         => ( 'plain' === $type ),
				'email'              => $this,
			];
		}


		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {

			return wc_get_template_html(
				$this->template_html,
				$this->get_content_args(),
				'',
				$this->template_base
			);
		}


		public function get_tracking_code_url( $tracking_number, $order = false ) {

			$view_order_url = false;

			if ( $order && is_a( $order, 'WC_Edostavka_Shipping_Order' ) ) {
				$view_order_url = $order->get_view_order_url();
			} elseif ( $this->object instanceof WC_Order ) {
				$view_order_url = $this->object->get_view_order_url();
			}

			if ( $view_order_url ) {
				$url = sprintf( '<a href="%s#wc-edostavka-tracking">%s</a>', $view_order_url, $tracking_number );

				return apply_filters( 'woocommerce_edostavka_email_tracking_core_url', $url, $tracking_number, $this->object );
			}
		}


		public function get_default_subject() {

			return '{site_title} - Заказ №{order_number} принят для доставки в Boxberry';
		}


		public function get_default_heading() {

			return 'Ваш заказ №{order_number} принят для доставки в Boxberry';
		}


		public function get_default_message_text() {

			return 'Ваш заказ #{order_number} на сайте <a href="{site_url}" target="_blank">{site_title}</a> был передан в <strong>курьерскую службу Boxberry</strong> с номером отслеживания <strong>{tracking_code}</strong>. Вы можете следить за статусом отправления <a href="https://boxberry.ru/tracking-page?id={tracking_code}">на сайте Boxberry</a>.';
		}


		public function get_default_additional_content() {

			return 'Спасибо что покупаете у нас. Если у вас возникнут проблемы с доставкой, пожалуйста, дайте нам знать.';
		}


		public function preview_emails() {

			if ( isset( $_GET['preview_edostavka_mail'] ) ) {
				if ( ! ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'edostavka-mail' ) ) ) {
					die( 'Security check' );
				}

				$email_id = wc_clean( $_REQUEST['email_id'] );
				$mailer   = WC()->mailer();
				$email    = null;

				foreach ( $mailer->get_emails() as $email_class ) {
					if ( $email_class->id !== $email_id ) {
						continue;
					}
					$email = $email_class;
				}

				if ( $email && is_a( $email, 'WC_Edostavka_Email' ) && $email->has_preview() ) {
					$email->preview_emails();
				}

				exit;
			}
		}


		/**
		 * @param  WC_Email $email
		 */
		public function add_preview_email_action( $email ) {

			echo '<td class="wc-email-settings-table-preview">';

			if ( is_a( $email, 'WC_Edostavka_Email' ) && $this->has_preview ) {
				printf( '<a class="button alignright" href="%s" target="_blank">%s</a>',
					wp_nonce_url( add_query_arg( [ 'preview_edostavka_mail' => true, 'email_id' => $email->id ], admin_url() ), 'edostavka-mail' ),
					__( 'Preview', 'woocommerce-edostavka' ) );
			}

			echo '</td>';
		}
	}


	return new Tracking_Email();

endif;
