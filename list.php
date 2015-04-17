<?php

require __DIR__  .'/vendor/autoload.php';
require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

// setup Guzzle client
$client = new GuzzleHttp\Client([
	'base_url' => 'https://'. MONEYBIRD_USERNAME.'.moneybird.nl/api/v1.0',
	'defaults' => [
		'auth' => [ MONEYBIRD_EMAIL, MONEYBIRD_PASSWORD, 'basic'],
		//'debug' => true,
		'headers' => [
			'Accept' => 'application/xml',
			'Content-Type' => 'application/xml'
		],
	]
]);


echo "Fetching invoices from MoneyBird." . PHP_EOL;
$response = $client->post('/invoices/filter/advanced.xml', [
	'body' => file_get_contents('filter.xml')
]);

$invoices = $response->xml();
echo count( $invoices ) . " invoices fetched." . PHP_EOL;

echo "Generating HTML for Belastingdienst.nl site." . PHP_EOL;

// loop through reverse charged invoices and generate correct HTML for Belastingdienst.nl
$html = '';
$index = 0;
foreach( $invoices as $invoice ) {

	// we need the vat number without the country code
	$vat_number = ( substr( $invoice->{"tax-number"}, 0,     2 ) === $invoice->country ) ? substr( $invoice->{"tax-number"}, 2 ) : $invoice->{"tax-number"};

	$row_html = generate_row_html( array(
		'index' => $index++,
		'vat_number' => $vat_number,
		'country_code' => $invoice->country,
		'total_services' => round( $invoice->{"total-price-incl-tax"} )
	) );

	$html .= $row_html . PHP_EOL . PHP_EOL;
}

// write final html to a file: /build/icp-YEAR-QUARTER.html
$filename = '/build/icp-'. date('Y') . '-' . ceil( ( ( date( 'm' ) - 3 ) / 3 ) ) . '.html';
file_put_contents( __DIR__ . $filename, $html );
echo "HTML generated and written to {$filename}." . PHP_EOL;



