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
      }).map(l => {
      	console.log(l);
         return {
            country: l[0].substring(8, 10),
            btwPerc: l[1].substring(0, 2),
            btwAmount: parseFloat(l[3].replace('.', '').replace(',', '.')),
            amount: parseFloat(l[2].replace('.', '').replace(',', '.'))
         }
      });

   let $context = window.jQuery('#MainContent');
   function setValue(el, value) {
   	el.val(value);
   	el.get(0).dispatchEvent(new Event('change'));
   	el.get(0).click();
   }
   
   items.forEach((l) => {
      // fill inputs in new row
      let $row = $context.find('.sub-panel-content h6').last().parent();
      
      if($row.find('select').val() != 'NULL') {
         // add new row
         $context.find('a.add').get(0).click();
         $row = $context.find('.sub-panel-content h6').last().parent();
      }
      
      setValue($row.find("select[id$='levland']"), countryMap[l.country]);
      setValue($row.find("select[id$='levgegsoort']"), "LEVGEGSOORT2");
      setValue($row.find("input[id$='levgegbtwbdrg']"), Math.floor(l.btwAmount));
      setValue($row.find("input[id$='levgegbtwperc']"), l.btwPerc);
      setValue($row.find("select[id$='levgegbtwtar']"), "LEVGEGBTWTAR1");
   })
   
}
