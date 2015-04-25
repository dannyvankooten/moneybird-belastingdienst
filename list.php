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

echo "Calculating totals per VAT number." . PHP_EOL;

// calculate the totals for each VAT number
$companies = array();
foreach( $invoices as $invoice ) {

	if( empty( $invoice->{"tax-number"} ) || empty( $invoice->country ) ) {
		continue;
	}

	if( $invoice->country === 'GR' ) {
		continue;
	}

	if( $invoice->currency !== 'EUR' ) {
		$total = $invoice->{"total-price-incl-tax"} * ( USD_TO_EUR_EXCHANGE_RATE / 1.02 );
	} else {
		$total = $invoice->{"total-price-incl-tax"};
	}

	// we need the vat number without the country code
	$country = substr( $invoice->{"tax-number"}, 0, 2 );
	$vat_number = (string) substr( $invoice->{"tax-number"}, 2 );

	// add to total or create new entry
	if( array_key_exists( $vat_number, $companies ) ) {
		$companies[ $vat_number ]->total_services += floor( $total );
	} else {
		$company = new StdClass;
		$company->total_services = floor( $total );
		$company->country_code = $country;
		$company->vat_number = $vat_number;
		$companies[ $vat_number ] = $company;
	}
}


// loop through reverse charged invoices and generate correct HTML for Belastingdienst.nl
echo "Generating HTML for Belastingdienst.nl site." . PHP_EOL;

$html = '';
$index = 0;
$total = 0;

foreach( $companies as $company ) {

	$row_html = generate_row_html( array(
		'index' => $index++,
		'vat_number' => $company->vat_number,
		'country_code' => $company->country_code,
		'total_services' => floor( $company->total_services )
	) );

	$html .= $row_html . PHP_EOL . PHP_EOL;

	$total += floor( $company->total_services );

	// quit at 100 rows
	if( $index >= 100 ) {
		echo "More than 100 company VAT numbers were found, but you can only submit up to a 100.... " . PHP_EOL;
		break;
	}
}

echo "Total reverse charged: " . $total . PHP_EOL;

// write final html to a file: /build/icp-YEAR-QUARTER.html
$filename = '/build/icp-'. date('Y') . '-' . ceil( ( ( date( 'm' ) - 3 ) / 3 ) ) . '.html';
file_put_contents( __DIR__ . $filename, $html );
echo "HTML generated and written to {$filename}." . PHP_EOL;



