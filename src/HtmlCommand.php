<?php

namespace WelMakkelijker\Command;

use WelMakkelijker\Customer;

use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp\Client;

/**
 * Class HtmlCommand
 *
 * @package WelMakkelijker\Command
 * @todo Refactor logic out of this class
 */
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
		$output->writeln( "<comment>Fetching invoices from MoneyBird.</comment>" );
		$response = $client->post('/invoices/filter/advanced.xml', [
			'body' => $payload
		]);

		$invoices = $response->xml();
		$output->writeln( "<comment>" . count( $invoices ) . " invoices fetched.</comment>" );

		// calculate the totals per VAT number
		$output->writeln( "<comment>Calculating totals per VAT number.</comment>" );
		$companies = array();
		foreach( $invoices as $invoice ) {

			if( empty( $invoice->{"tax-number"} ) ) {
				$output->writeln( "<comment>Skipping invoice #{$invoice->id} because of empty tax number.</comment>" );
				continue;
			} elseif( empty( $invoice->country ) ) {
				$output->writeln( "<comment>Skipping invoice #{$invoice->id} because of empty country.</comment>" );
				continue;
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
				$output->writeln( "<comment>Adding EUR{$total} to {$country_code}{$tax_number}.</comment>" );
				$companies[ $tax_number ]->addValue( $total );
			} else {
				$output->writeln( "<comment>Adding {$country_code}{$tax_number} with a total of EUR{$total}.</comment>");
				$customer = new Customer( $total, $country_code, $tax_number );
				$companies[ $tax_number ] = $customer;
			}
		}

		// Sort (highest total to lowest)
		usort( $companies, function( Customer $a, Customer $b ) {
			if ($a->getTotalValue() == $b->getTotalValue() ) return 0;
            return ($a->getTotalValue() > $b->getTotalValue()) ? -1 : 1;
		});

		// Start generating HTML
		$output->writeln( "<comment>Generating HTML for Belastingdienst.nl site.</comment>" );

		// loop through reverse charged invoices and generate correct HTML for Belastingdienst.nl
		$html = '';
		$index = 0;
		$total = 0;

		/**
		 * @var Customer $customer
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
				$output->writeln( "<error>More than 100 company VAT numbers were found, but you can only submit up to a 100....</error>" );
				$output->writeln( "<error>Contact your tax advisor...</error>" );
				break;
			}
		}

		// output table
		$table = $this->getHelper('table');
		$table->setHeaders(array('VAT Number', 'Amount'));
		$table->setRows( array_map( function($c) {
			/** @var Customer $c */
			return array( $c->country_code . $c->tax_number, '€' . $c->getTotalValue() );
		}, $companies ) );
		$table->render($output);

		$output->writeln( "<info>Total reverse charged: €" . floor( $total ) .'</info>');

		// write final html to a file: /build/icp-YEAR-QUARTER.html
		$filename = $this->getFileName( $input->getOption('period'));
		file_put_contents( __DIR__ . '/../' . $filename, $html );
		$output->writeln( "<comment>HTML generated and written to {$filename}.</comment>" );
	}

	/**
	 * @param $period
	 *
	 * @return string
	 */
	protected function getFileName( $period ) {
		if( $period == 'last_quarter' ) {
			return 'build/icp-' . date( 'Y' ) . '-q' . ceil( ( ( date( 'm' ) - 3 ) / 3 ) ) . '.html';
		}

		return 'build/icp-' . date( 'Y' ) . '-m' . ( date( 'm' ) - 1 ) . '.html';
	}

}

