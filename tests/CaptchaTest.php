<?php

declare(strict_types=1);

namespace App\Tests;

use Nette;
use Tester;
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';


class CaptchaTest extends Tester\TestCase
{
	private $container;
        private $captcha;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
                $this->captcha = $container->getByType('\App\Model\Captcha');
	}


	public function setUp()
	{
	}


	public function testNumberToText()
	{
            $text = $this->captcha->numberToText(0);
            Assert::equal("nula", $text);
            $text = $this->captcha->numberToText(27);
            Assert::equal("dvacetsedm", $text);
            $text = $this->captcha->numberToText(99);
            Assert::same('devadesátdevět', $text);
	}
}


$container = \App\Bootstrap::bootForTests()
	->createContainer();

$test = new CaptchaTest($container);
$test->run();
