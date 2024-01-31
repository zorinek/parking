#!/usr/bin/env php
<?php

use App\Model;

# Autoloading tříd přes Composer - tedy i naší Bootstrap třídy
require __DIR__ . '/../vendor/autoload.php';

# Necháme konfigurátor, aby nám sestavil DI kontejner
$container = App\Bootstrap::bootForCron()
	->createContainer();

$c = $container->callMethod(function (App\Model\Cron $cron) {

});