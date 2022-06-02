<?php
/**
 * Custom product field Date Picker data object.
 *
 * @link       https://themehigh.com
 * @since      2.3.0
 *
 * @package    woocommerce-extra-product-options-pro
 * @subpackage woocommerce-extra-product-options-pro/includes/model/fields
 */
if(!defined('WPINC')){	die; }

if(!class_exists('WEPO_Product_Field_DateRangePicker')):

class WEPO_Product_Field_DateRangePicker extends WEPO_Product_Field{
	public $pattern = array(			
			'/d/', '/j/', '/l/', '/z/', '/S/', //day (day of the month, 3 letter name of the day, full name of the day, day of the year, )			
			'/F/', '/M/', '/n/', '/m/', //month (Month name full, Month name short, numeric month no leading zeros, numeric month leading zeros)			
			'/Y/', '/y/' //year (full numeric year, numeric year: 2 digit)
		);
		
	public $replace = array(
			'dd','d','DD','o','',
			'MM','M','m','mm',
			'yy','y'
		);
		
	// public $default_date = '';
	public $date_format = '';
	public $start_date = '';
	public $end_date = '';
	public $min_date = '';
	public $max_date = '';
	public $min_year = '';
	public $max_year = '';

	public $enable_time_picker		= false;
	public $show_time_only			= false;
	public $show_single_datepicker	= false;

	// public $year_range = '';
	// public $number_of_months = '';
	// public $disabled_days = array();
	// public $disabled_dates = array();
	public $readonly = false;
	
	public function __construct() {
		$this->type = 'daterangepicker';
	}
}

endif;