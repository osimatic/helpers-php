<?php

namespace Osimatic\Helpers\Bank;

use Symfony\Component\Intl\Currencies;

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
	 * @param string $countryCode
	 * @return int
	 */
	public static function getNumericCodeOfCountry(string $countryCode): int
	{
		return self::getNumericCode(self::getCurrencyOfCountry($countryCode));
	}

	/**
	 * @param string $currencyCode
	 * @return int
	 */
	public static function getNumericCode(string $currencyCode): int
	{
		return Currencies::getNumericCode($currencyCode);
	}

	/**
	 * @param string $currencyCode
	 * @return bool
	 */
	public static function check(string $currencyCode): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($currencyCode, new \Symfony\Component\Validator\Constraints\Currency())->count() === 0;
	}

	/**
	 * @param float $number
	 * @param string $currency
	 * @param int $decimals
	 * @return string
	 */
	public static function format(float $number, string $currency, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
		$fmt->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return self::clean($fmt->formatCurrency($number, $currency));
	}

	/**
	 * @param float $number
	 * @param string $currency
	 * @param int $decimals
	 * @return string
	 */
	public static function formatWithCode(float $number, string $currency, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return self::clean($fmt->format($number)).' '.$currency;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private static function clean(string $str): string
	{
		// retrait de l'espace insécable
		//$str = preg_replace("\u{00a0}", '', $str);
		//$str = preg_replace("\u{0020}", '', $str);
		return str_replace("\xE2\x80\xAF", ' ', $str);
	}

}
