<?php

namespace Osimatic\Number;

/**
 * Class Percent
 * Provides utilities for formatting percentage values
 */
class Percent
{
	/**
	 * Formats a number as a percentage string using locale-specific formatting
	 * @param float|int $number the number to format (e.g., 75 for 75%)
	 * @param int $decimals the number of decimal places to display (default: 2)
	 * @return string the formatted percentage string
	 */
	public static function format(float|int $number, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::PERCENT);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return $fmt->format($number/100);
	}

}