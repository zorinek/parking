<?php

declare(strict_types=1);

namespace App\Forms;

class CustomFormRules
{

	const CUSTOM_PASSWORD = 'CustomFormRules::validateCustomPassword';

	public static function validateCustomPassword($control, $type)
	{
		$val = $control->value;
		$pass = true;
		$numbers = '/[0-9]/';
		$special_chars = "/[ `!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?~]/";

		if ($type > 0 && mb_strtoupper($val) === $val)
		{
			$pass = false;
		}

		if ($type > 0 && !preg_match($numbers, $val))
		{
			$pass = false;
		}

		if ($type > 1 && mb_strtolower($val) === $val)
		{
			$pass = false;
		}

		if ($type > 2 && !preg_match($special_chars, $val))
		{
			$pass = false;
		}
		return $pass;
	}

}
