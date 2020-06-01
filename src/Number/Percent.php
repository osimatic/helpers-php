<?php

namespace Osimatic\Helpers\Number;

class Percent
{
	/**
	 * @param number $number
	 * @param int $decimals
	 * @return string
	 */
	public static function format(number $number, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::PERCENT);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return $fmt->format($number);
	}

}