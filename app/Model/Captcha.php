<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;

/**
 * Captcha
 */
final class Captcha
{

	use Nette\SmartObject;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}

	public function generate($division, $count_numbers, $digits)
	{
		$numbers = [];
		$linked = "";
		$sum = 0;
		for ($i = 0; $i < $count_numbers; $i++)
		{
			$num = intval(Random::generate($digits, "0-9"));
			if ($i > 0)
			{
				$linked .= "plus";
			}
			$linked .= $this->numberToText($num);
			$sum += $num;
		}

		$splited = mb_str_split($linked, $division);
		$joined = join(" ", $splited);
		return ["text" => $joined, "sum" => $sum];
	}

	public function numberToText($number)
	{
		$txt_num = $this->translator->translate("captcha_numbers." . $number);
		if ($txt_num == (string) $number)
		{
			$floored = floor($number / 10) * 10;
			$txt = $this->translator->translate("captcha_numbers." . $floored) . $this->translator->translate("captcha_numbers." . ($number - $floored));
		} 
		else
		{
			$txt = $txt_num;
		}
		return $txt;
	}

	public function decode($text)
	{
		$without_spaces = str_replace(" ", "", $text);
		$without_plus = explode("plus", $without_spaces);
		$sum = 0;
		foreach ($without_plus as $num)
		{
			$sum += $this->textToNum($num);
		}
		return $sum;
	}

	public function textToNum($text)
	{
		$txt_num = $this->translator->translate("captcha_numbers." . $text);
		if ($txt_num != $text)
		{
			$txt = intval($txt_num);
		} 
		else
		{
			$exploded = [];
			for ($i = 20; $i < 100; $i = $i + 10)
			{
				$exploded = explode($this->translator->translate("captcha_numbers." . $i), $text);
				if (count($exploded) == 2)
				{
					break;
				}
			}
			$txt = $i + intval($this->translator->translate("captcha_numbers." . $exploded[1]));
		}
		return $txt;
	}

}
