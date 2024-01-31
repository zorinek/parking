<?php

declare(strict_types=1);

namespace App;

use Nette\Configurator;

class Bootstrap
{

	public static function boot($debugMode = false): Configurator
	{
		$configurator = new Configurator;
		if (!$debugMode && isset($_SERVER['HTTP_HOST']) && substr($_SERVER['HTTP_HOST'], 0, 9) == "localhost")
		{
			$debugMode = true;
		}
		//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
		$configurator->setDebugMode($debugMode); // enable for your remote IP
		$configurator->enableTracy(__DIR__ . '/../log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory(__DIR__ . '/../temp');

		$configurator->createRobotLoader()
				->addDirectory(__DIR__)
				->register();

		$configurator
				->addConfig(__DIR__ . '/config/common.neon')
				->addConfig(__DIR__ . '/config/local.neon');

		return $configurator;
	}

	public static function bootForTests(): Configurator
	{
		$configurator = self::boot();
		\Tester\Environment::setup();
		return $configurator;
	}

	public static function bootForCron(): Configurator
	{
		# Debug mód pouze pokud existuje --debug přepínač
		$debugMode = in_array('--debug', $_SERVER['argv'], true);
//                return self::boot($res);

		$configurator = new Configurator;
		if (!$debugMode && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "localhost")
		{
			$debugMode = true;
		}
		//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
		$configurator->setDebugMode($debugMode); // enable for your remote IP
		$configurator->enableTracy(__DIR__ . '/../log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory(__DIR__ . '/../temp_cron');

		$configurator->createRobotLoader()
				->addDirectory(__DIR__)
				->register();

		$configurator
				->addConfig(__DIR__ . '/config/common.neon')
				->addConfig(__DIR__ . '/config/local.neon');

		return $configurator;
	}

}
