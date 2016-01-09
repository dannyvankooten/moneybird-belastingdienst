MoneyBird - Belastingdienst
==================

Deze library helpt je met het doen van je aangifte bij de Belastingdienst door HTML te genereren van compleet ingevulde formulieren aan de hand van je MoneyBird data. Hierbij kunnen natuurlijk geen garanties worden geboden.

Gebruik
==========

_Environment Vars_

```sh
MONEYBIRD_USERNAME=jouwsubdomein
MONEYBIRD_EMAIL=john@doedoe.com
MONEYBIRD_PASSWORD=geheim
```

Op dit moment wordt enkel het formulier voor de ICP opgave ondersteund, omdat die simpelweg het kutst is om handmatig te doen.

```py
python icp.py
```

VATMOSS coming soon.

License
========

(MIT License)

Copyright (c) 2014-2016 Danny van Kooten hi@dvk.co

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.