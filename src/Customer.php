<?php

class Customer {

	public $total_value = 0;

	public $country_code = '';

	public $tax_number = '';

	/**
	 * @param $value
	 * @param $country_code
	 * @param $tax_number
	 */
	public function __construct( $value, $country_code, $tax_number ) {
		$this->setCountryCode( $country_code );
		$this->tax_number = $tax_number;
		$this->addValue( $value );
	}

	/**
	 * @param $value
	 */
	public function addValue( $value ) {
		$this->total_value += $value;
	}

	/**
	 * @return float
	 */
	public function getTotalValue() {
		return round( $this->total_value, 2 );
	}

	/**
	 * @param $country_code
	 */
	public function setCountryCode( $country_code ) {
		$replacements = array(
			'GR' => 'EL'
		);
		$country_code = str_replace( array_keys( $replacements ), array_values( $replacements ), $country_code );
		$this->country_code = $country_code;
	}
}