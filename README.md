MoneybBird ICP opgave
==================

This script will generate the expected HTML for the Belastingdienst.nl site when doing your "ICP opgave". This saves you 
from manually having to enter a shitload of data.

Usage
==========

After cloning the repository, create a `config.php` file in the root of your project with the following contents.

```php
<?php
define( 'MONEYBIRD_USERNAME', 'your-username' );
define( 'MONEYBIRD_EMAIL', 'your-email' );
define( 'MONEYBIRD_PASSWORD', 'your-password' );
define( 'USD_TO_EUR_EXCHANGE_RATE', 0.90 );
```

You can then run the following command to generate the HTML. It will dump the HTML to a file in the `build/` subdirectory.

```
php app moneybird-ic:html --period=last_quarter
```

License
========

(MIT License)

Copyright (c) 2014-2015 Danny van Kooten hi@dvk.co

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.