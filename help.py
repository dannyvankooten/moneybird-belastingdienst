#!/usr/bin/env python3

import sys
from country_codes import ISO3166
from math import floor

print("-------------")

if len(sys.argv) <= 1:
    print("Usage: help.py \"<table rows from https://moneybird.com/154876003116647713/report/tax/period:prev_quarter,country:overview/ here>\"")
    sys.exit(0)

data = sys.argv[1].strip().splitlines()
total_vat_amount = 0.0
out = ""

for line in data:
    parts = line.split("\t")
    if not '% VAT' in parts[0]:
        print("WARNING: Skipping line " + line)
        continue
    country_name = ISO3166[parts[0].split(" ")[2]]

    if country_name == "Netherlands":
        print("WARNING: Skipping line " + line)
        continue

    vat_amount = floor(float(parts[4].replace(".", "").replace(",", ".")))
    vat_rate = parts[2].replace("Dienst en ", "").replace("% btw", "")
    total_amount = floor(float(parts[3].replace(".", "").replace(",", ".")))
    total_vat_amount += vat_amount

    out += "{:<20} {:>20} {:>20} {:>20}".format(country_name, "€" + str(vat_amount), vat_rate, "€" + str(total_amount) + "\n")
print("-------------")
print("{:<20} {:>20} {:>20} {:>20}".format("Geleverd in land", "BTW bedrag", "BTW tarief", "Totaalbedrag"))
print("-" * len("Geleverd in land               BTW bedrag           BTW tarief         Totaalbedrag"))
print(out)

print("-" * len("Geleverd in land               BTW bedrag           BTW tarief         Totaalbedrag"))
print("{:<20} {:>20} {:>20} {:>20}".format("", "", "", "Total VAT: €" + str(total_vat_amount)))
