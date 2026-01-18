<?php

namespace Osimatic\Bank;

use Symfony\Component\Intl\Currencies;

/**
 * Utility class for currency operations and formatting.
 * Provides methods for:
 * - Getting currency information by country code
 * - Validating currency codes
 * - Formatting currency amounts with localization
 * - Converting currency codes to numeric codes
 */
class Currency
{
	/**
	 * Gets the currency code for a specific country.
	 * Uses NumberFormatter with the country's locale to determine the currency.
	 * @param string $countryCode ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return string The ISO 4217 currency code (e.g., 'EUR', 'USD')
	 */
	public static function getCurrencyOfCountry(string $countryCode): string
	{
		return (new \NumberFormatter(
			\Osimatic\Location\Country::getLocaleByCountryCode($countryCode),
			\NumberFormatter::CURRENCY
		))->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
	}

	/**
	 * Gets the numeric ISO 4217 code for a country's currency.
	 * Convenience method that combines getCurrencyOfCountry and getNumericCode.
	 * @param string $countryCode ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return int The numeric ISO 4217 currency code (e.g., 978 for EUR, 840 for USD)
	 */
	public static function getNumericCodeOfCountry(string $countryCode): int
	{
		return self::getNumericCode(self::getCurrencyOfCountry($countryCode));
	}

	/**
	 * Gets the numeric ISO 4217 code for a currency.
	 * Uses Symfony's Currencies component to retrieve the numeric code.
	 * @param string $currencyCode ISO 4217 currency code (e.g., 'EUR', 'USD')
	 * @return int The numeric ISO 4217 currency code (e.g., 978 for EUR, 840 for USD)
	 */
	public static function getNumericCode(string $currencyCode): int
	{
		return Currencies::getNumericCode($currencyCode);
	}

	/**
	 * Validates if a currency code is valid.
	 * Uses Symfony Validator component to check against ISO 4217 standard.
	 * @param string $currencyCode The currency code to validate (e.g., 'EUR', 'USD')
	 * @return bool True if the currency code is valid, false otherwise
	 */
	public static function check(string $currencyCode): bool
	{
		if (empty($currencyCode)) {
			return false;
		}

		return \Osimatic\Validator\Validator::getInstance()->validate($currencyCode, new \Symfony\Component\Validator\Constraints\Currency())->count() === 0;
	}

	/**
	 * Formats a number as a currency amount with the currency symbol.
	 * Uses the current locale for formatting (e.g., "€10.50" or "$10.50").
	 * Removes non-breaking spaces for cleaner output.
	 * @param float $number The amount to format
	 * @param string $currency ISO 4217 currency code (e.g., 'EUR', 'USD')
	 * @param int $decimals Number of decimal places (default: 2)
	 * @return string The formatted currency string with symbol
	 */
	public static function format(float $number, string $currency, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
		$fmt->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->formatCurrency($number, $currency));
	}

	/**
	 * Formats a number with the currency code appended.
	 * Uses decimal formatting and appends the currency code (e.g., "10.50 EUR").
	 * Removes non-breaking spaces for cleaner output.
	 * @param float $number The amount to format
	 * @param string $currency ISO 4217 currency code (e.g., 'EUR', 'USD')
	 * @param int $decimals Number of decimal places (default: 2)
	 * @return string The formatted number with currency code appended
	 */
	public static function formatWithCode(float $number, string $currency, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number)).' '.$currency;
	}

}
