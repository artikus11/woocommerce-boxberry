<?php

namespace Boxberry\Woocommerce;

class WC_Boxberry_CourierAfter_Method extends WC_Boxberry_Parent_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'boxberry_courier_after';
		$this->method_title          = __( 'Boxberry Courier Payment After', 'boxberry' );
		$this->instance_form_fields = array(

		);
		$this->self_type = false;
		$this->payment_after = true;
		parent::__construct( $instance_id );
		$this->key            = $this->get_option( 'key' );
	}
}