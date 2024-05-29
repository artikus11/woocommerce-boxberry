<?php

namespace Boxberry\Woocommerce;

class WC_Boxberry_SelfAfter_Method extends WC_Boxberry_Parent_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'boxberry_self_after';
		$this->method_title          = __( 'Boxberry Self Payment After', 'boxberry' );
		$this->instance_form_fields = array(

		);
		$this->self_type = true;
		$this->payment_after = true;
		parent::__construct( $instance_id );
		$this->default_weight   		  = $this->get_option( 'default_weight' );
		$this->key            = $this->get_option( 'key' );
	}
}