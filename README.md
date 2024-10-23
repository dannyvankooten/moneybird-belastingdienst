Autofill VATMOSS formulier op Belastingdienst.nl. Provided as is, use at your own risk. 

1. Add function via browser console:

```js
function vatmossbot(data) {

   const countryMap = {
      'NL': 'LAND1',
      'BE': 'LAND2',
      'BG': 'LAND3',
      'CY': 'LAND4',
      'DK': 'LAND5',
      'DE': 'LAND6',
      'EE': 'LAND7',
      'FI': 'LAND8',
      'FR': 'LAND9',
      'GR': 'LAND10',
      'HU': 'LAND11',
      'IE': 'LAND12',
      'IT': 'LAND13',
      'HR': 'LAND14',
      'LV': 'LAND15',
      'LT': 'LAND16',
      'LU': 'LAND17',
      'MT': 'LAND18',
      'NI': 'LAND19',
      'AT': 'LAND20',
      'PL': 'LAND21',
      'PT': 'LAND22',
      'RO': 'LAND23',
      'SI': 'LAND24',
      'SK': 'LAND25',
      'ES': 'LAND26',
      'CZ': 'LAND27',
      'SE': 'LAND28',
   };

   let items = data.split("\n")
      .filter(l => l.trim().length > 0)
      .map(l => {
         return l.split('\t')
            .map(j => j.trim())
            .filter(j => j.length > 0)
            .filter(j => j != 'OBU')
      }).map(l => {
         console.log(l);
         return {
            country: l[0].substring(8, 10),
            btwPerc: l[1].replace('Dienst en ', '').replace('% btw', '').substring(0, 2),
            btwAmount: parseFloat(l[3].replace('.', '').replace(',', '.')),
            amount: parseFloat(l[2].replace('.', '').replace(',', '.'))
         }
      });

   let context = document.querySelector('#leveringeninvoer_levpanlijst1_levpan_panel');
   if (!context) {
      throw new Error('Row wrapping element not found');
   }
  let rows = context.querySelector('.table-container').nextElementSibling;

   function setValue(el, value) {
      el.setAttribute('value', value);
      el.value = value;
      el.click();
      el.dispatchEvent(new Event('change'));
      el.click();
   }

   items.forEach((l) => {
      // fill inputs in new row
      let row = rows.children[rows.children.length - 1];

      if(row.querySelector('select').value != 'NULL') {
         // add new row
         context.querySelector('.toevoeg_link').click()
         row = rows.children[rows.children.length - 1];
      }

      setValue(row.querySelector("select[id$='levland']"), countryMap[l.country]);
      setValue(row.querySelector("select[id$='levgegsoort']"), "LEVGEGSOORT2");
      setValue(row.querySelector("input[id$='levgegbtwbdrg']"), Math.floor(l.btwAmount));
      setValue(row.querySelector("input[id$='levgegbtwperc']"), l.btwPerc);
      setValue(row.querySelector("select[id$='levgegbtwtar']"), "LEVGEGBTWTAR1");
   })
}
```

2. Copy "BTW Overzicht" from Moneybird

```
https://moneybird.com/<administration-id>/report/tax/period:prev_quarter,country:overview/
```

3. Pass copied table as multiline string to `vatmossbot` function.

```js
vatmossbot(`20% VAT SK Standard     20% btw  50,91  10,18
23% VAT PL Standard     23% btw  48,02   11,04
....
`)
```

Example data comes from Moneybird "BTW Overzicht". Change the script's country parser to extract country code from your tax rate names and you should be all set!

Make sure there is a tab (\t) between each column of data.

## License

GPLv3
