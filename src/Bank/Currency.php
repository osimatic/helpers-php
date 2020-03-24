<?php

namespace Osimatic\Helpers\Bank;

class Currency
{
	/**
	 * @param string $countryCode
	 * @return string
	 */
	public static function getCurrencyOfCountry(string $countryCode): string
	{
		return (new \NumberFormatter(
			\Osimatic\Helpers\Location\Country::getLocaleByCountryCode($countryCode),
			\NumberFormatter::CURRENCY
		))->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
	}

	/**
	 * @param $number
	 * @param string $currency
	 * @param int $decimals
	 * @return string
	 */
	public static function format($number, string $currency, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
		$fmt->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return $fmt->formatCurrency($number, $currency);
	}

	/**
	 * @param $number
	 * @param string $currency
	 * @param int $decimals
	 * @return string
	 */
	public static function formatWithCode($number, string $currency, int $decimals=2)
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return $fmt->format($number).' '.$currency;
	}

}
