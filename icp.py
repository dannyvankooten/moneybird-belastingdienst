import requests
import xml.etree.ElementTree as ET
import operator, math, os, argparse

# Read CLI arguments
parser = argparse.ArgumentParser(description='Genereert formulier HTML voor de Belastingdienst opgave site.')
parser.add_argument('--tax_rate_id', help='De tax rate ID die bij dit formulier hoort in MoneyBird.', default=501022)
parser.add_argument('--period', help='De periode waarover je aangifte doet. Standaard is "last_month".', default="last_month")
args = parser.parse_args()

# Read some environment variables
moneybird_username = os.environ.get('MONEYBIRD_USERNAME')
moneybird_email = os.environ.get('MONEYBIRD_EMAIL')
moneybird_password = os.environ.get('MONEYBIRD_PASSWORD')

# Define Company class
class Company:
	total = 0.00
	vat_number = ""

	def __init__(self, vat_number):
		self.vat_number = vat_number

# Read filter.xml into variable
payload = open('filters/icp.xml', 'r').read().replace('{{tax_rate_id}}', str(args.tax_rate_id)).replace('{{period}}',args.period)

# Request MoneyBird API
response = requests.post('https://'+moneybird_username+'.moneybird.nl/api/v1.0/invoices/filter/advanced.xml',
	auth=(moneybird_email, moneybird_password),
	data=payload,
	headers={
	'Content-Type': 'application/xml',
	'Accept': 'application/xml'
	}
)

# Turn response into XML list
root = ET.fromstring(response.content)
companies = {}

# Calculate total for each company
for invoice in root:
	tax_number = invoice.find('tax-number').text
	total = float(invoice.find('total-price-incl-tax-base').text)

	if not companies.has_key( tax_number ):
		companies[ tax_number ] = Company( tax_number )

	companies[tax_number].total += total

# sort companies (highest total first)
companies = sorted(companies.values(), key=operator.attrgetter('total'),reverse=True)

# Generate HTML
html = ""
template = open('templates/icp.html', 'r').read()

for index, company in enumerate(companies):
	html = html + template.replace("{{index}}", str(index)).replace('{{vat_number}}', company.vat_number[2:len(company.vat_number)]).replace('{{total_services}}', str(math.floor(company.total))).replace('{{total_goods}}', '').replace('{{country}}', company.vat_number[0:2])

# Write to file and open
file = open('/tmp/form.html','w').write(html)
os.system('open /tmp/form.html -a "Sublime Text"')