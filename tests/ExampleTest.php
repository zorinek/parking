<?php

declare(strict_types=1);

namespace App\Tests;

use Nette;
use Tester;
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';


class ExampleTest extends Tester\TestCase
{
	private $container;


	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}


	public function setUp()
	{
	}


	public function testSomething()
	{
//            Assert::count(5, [1,2,3,4,5]);
		Assert::true(true);
	}
}


$container = \App\Bootstrap::bootForTests()
	->createContainer();

$test = new ExampleTest($container);
$test->run();
