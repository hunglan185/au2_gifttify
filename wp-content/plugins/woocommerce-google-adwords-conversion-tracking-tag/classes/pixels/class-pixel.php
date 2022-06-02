<?php

namespace WCPM\Classes\Pixels;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Pixel {

	use Trait_Product;
	use Trait_Shop;

	protected $add_cart_data;
	protected $aw_merchant_id;
	protected $conversion_id;
	protected $conversion_label;
	protected $dynamic_remarketing;
	protected $google_business_vertical;
	protected $ip;
	protected $order_total_logic;
	protected $product_identifier;
	protected $options;
	protected $options_obj;
	protected $dyn_r_id_type;
	protected $pixel_name = '';

	public function __construct( $options ) {
		/*
		 * Initialize options
		 */
		$this->options     = $options;
		$this->options_obj = $this->get_options_object($options);

		$this->options_obj->shop->currency = get_woocommerce_currency();

		$this->order_total_logic   = $this->options['shop']['order_total_logic'];
		$this->add_cart_data       = (bool) $this->options['google']['ads']['aw_merchant_id'];
		$this->aw_merchant_id      = $this->options['google']['ads']['aw_merchant_id'];
		$this->conversion_id       = $this->options['google']['ads']['conversion_id'];
		$this->conversion_label    = $this->options['google']['ads']['conversion_label'];
		$this->dynamic_remarketing = $this->options['google']['ads']['dynamic_remarketing'];
		$this->product_identifier  = $this->options['google']['ads']['product_identifier'];
	}
}
