<?php

namespace Boxberry\Woocommerce;

class WC_Boxberry_Courier_Method extends WC_Boxberry_Parent_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'boxberry_courier';
		$this->method_title          = __( 'Boxberry Courier', 'boxberry' );
		$this->instance_form_fields = array(

		);
		$this->self_type = false;
		$this->payment_after = false;
		parent::__construct( $instance_id );
		$this->key            = $this->get_option( 'key' );
	}
}