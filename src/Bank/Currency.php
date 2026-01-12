<?php

namespace Osimatic\Bank;

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
			\Osimatic\Location\Country::getLocaleByCountryCode($countryCode),
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
		if (empty($currencyCode)) {
			return false;
		}

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
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->formatCurrency($number, $currency));
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
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number)).' '.$currency;
	}

}
