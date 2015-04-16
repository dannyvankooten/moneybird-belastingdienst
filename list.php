<?php

require __DIR__  .'/vendor/autoload.php';
require __DIR__ . '/config.php';

// setup Guzzle client
$client = new GuzzleHttp\Client([
	'base_url' => 'https://'. MONEYBIRD_USERNAME.'.moneybird.nl/api/v1.0',
	'defaults' => [
		'auth' => [ MONEYBIRD_EMAIL, MONEYBIRD_PASSWORD, 'basic'],
		'headers' => [
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		],
		'debug' => true
	]
]);

// fetch invoices
$response = $client->post('/invoices/filter/advanced.json', [
	'body' => [
		//'filter' => 'period:last_quarter,tax_percentage:'.MONEYBIRD_REVERSE_CHARGE_TAX_ID
		'invoice_filter' => [

			'filter_rule' => [
				[
					'filter_scope' => 'tax_percentage',
					'tax_rate_id' => MONEYBIRD_REVERSE_CHARGE_TAX_ID
				],
				[
					'filter_scope' => 'invoice_date',
					'date_scope' => 'last_quarter'
				]

			]

		]


	]
]);

// get JSON array of invoices
$invoices = $response->json();
echo count( $invoices ) . " invoices fetched." . PHP_EOL;

// loop through reverse charged invoices and output correct HTML for Belastingdienst.nl
// todo