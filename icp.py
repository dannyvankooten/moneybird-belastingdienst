import sys
import requests
import xml.etree.ElementTree as ET
import operator
import math
import os

# TODO: Allow specifying a filter file as argument
# TODO: Allow specifiying tax ID as argument

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

	def __repr__(self):
		return self.vat_number + ": " + str( self.total) + "\n"

# Read filter.xml into variable
payload = open('filters/icp.xml', 'r').read().replace('{{period}}','last_month')

# Request MoneyBird API
r = requests.post('https://'+moneybird_username+'.moneybird.nl/api/v1.0/invoices/filter/advanced.xml',
	auth=(moneybird_email, moneybird_password),
	data=payload,
	headers={
	'Content-Type': 'application/xml',
	'Accept': 'application/xml'
	}
)
response = r.content

# Turn response into XML list
root = ET.fromstring(response)
companies = {}

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
	html = html + template.replace("{{INDEX}}", str(index)).replace('{{VAT_NUMBER}}', company.vat_number[2:len(company.vat_number)]).replace('{{TOTAL_SERVICES}}', str(math.floor(company.total))).replace('{{TOTAL_GOODS}}', '').replace('{{COUNTRY}}', company.vat_number[0:2])

file = open('/tmp/form.html','w').write(html)
os.system('open /tmp/form.html -a "Sublime Text"')