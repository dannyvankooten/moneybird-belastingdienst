#!/usr/bin/env php
<?php

use WelMakkelijker\Command\HtmlCommand;
use Symfony\Component\Console\Application;

error_reporting( E_ALL );

require __DIR__.'/vendor/autoload.php';

// fire up new application instance
$application = new Application();

// register commands
$application->add(new HtmlCommand());

// run
$application->run();