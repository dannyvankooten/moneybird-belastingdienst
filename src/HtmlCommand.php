<?php

namespace Command;

use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp\Client;

class HtmlCommand extends Command {

	protected function configure() {
		$this
			->setName('moneybird-ic:html')
			->setDescription( 'Generate a list of IC services (in HTML format).')
			->addOption(
				'period',
				null,
				InputOption::VALUE_OPTIONAL,
				'The period for which the HTML should be generated (eg: last_quarter)',
				'last_quarter'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {

		// get config vars. todo: use phpenv
		require __DIR__ . '/../config.php';

		// setup Guzzle client
		$client = new \GuzzleHttp\Client([
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

		// modify filter with given period
		$payload = file_get_contents( __DIR__ . '/filter.xml' );
		$payload = str_replace( '{{period}}', $input->getOption( 'period'), $payload );

		// request invoices from moneybird api
		$output->writeln( "Fetching invoices from MoneyBird." );
		$response = $client->post('/invoices/filter/advanced.xml', [
			'body' => $payload
		]);

		$invoices = $response->xml();
		$output->writeln( count( $invoices ) . " invoices fetched." );

		// calculate the totals per VAT number
		$output->writeln( "Calculating totals per VAT number." );
		$companies = array();
		foreach( $invoices as $invoice ) {

			if( empty( $invoice->{"tax-number"} ) ) {
				$output->writeln( "Skipping invoice #{$invoice->id} because of empty tax number." );
				continue;
			} elseif( empty( $invoice->country ) ) {
				$output->writeln( "Skipping invoice because of empty country." );
				continue;
			}

			// skip invoices with a negative amount (because of refunds and currency values)
			if( $invoice->{"total-price-incl-tax"} < 0 ) {
				$output->writeln( sprintf( "Skipping invoice #%s because of total %s", $invoice->id, $invoice->currency . $invoice->{"total-price-incl-tax"} ) );

				// skip this or include this?
				//continue;
			}

			// use conversion offered by MoneyBird
			if( $invoice->currency !== 'EUR' ) {
				$total = $invoice->{"total-price-incl-tax-base"};
			} else {
				$total = $invoice->{"total-price-incl-tax"};
			}

			// we need the vat number without the country code
			$country_code = substr( $invoice->{"tax-number"}, 0, 2 );
			$tax_number = (string) substr( $invoice->{"tax-number"}, 2 );

			// add to total or create new entry
			if( array_key_exists( $tax_number, $companies ) ) {
				$output->writeln( "Adding value to existing customer." );
				$companies[ $tax_number ]->addValue( $total );
			} else {
				$customer = new \Customer( $total, $country_code, $tax_number );
				$companies[ $tax_number ] = $customer;
			}
		}

		// loop through reverse charged invoices and generate correct HTML for Belastingdienst.nl
		$output->writeln( "Generating HTML for Belastingdienst.nl site." );

		$html = '';
		$index = 0;
		$total = 0;

		/**
		 * @var \Customer $customer
		 */
		foreach( $companies as $customer ) {

			$row_html = generate_row_html( array(
				'index' => $index++,
				'vat_number' => $customer->tax_number,
				'country_code' => $customer->country_code,
				'total_services' => $customer->getTotalValue()
			) );

			$html .= $row_html . PHP_EOL . PHP_EOL;

			$total += $customer->getTotalValue();

			// quit at 100 rows
			if( $index >= 100 ) {
				$output->writeln( "More than 100 company VAT numbers were found, but you can only submit up to a 100.... " );
				break;
			}
		}

		$output->writeln( "Total reverse charged: " . floor( $total ) );

		// write final html to a file: /build/icp-YEAR-QUARTER.html
		$filename = '/build/icp-'. date('Y') . '-' . ceil( ( ( date( 'm' ) - 3 ) / 3 ) ) . '.html';
		file_put_contents( __DIR__ . '/../' . $filename, $html );
		$output->writeln( "HTML generated and written to {$filename}." );


	}

}

