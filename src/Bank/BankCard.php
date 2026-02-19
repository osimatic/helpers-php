<?php

namespace Osimatic\Bank;

use Osimatic\Calendar\Date;

/**
 * Utility class for bank card operations
 * Provides methods for validating, formatting, and parsing bank card information
 */
class BankCard
{
	/**
	 * Parse an expiration date string into a DateTime object
	 * Handles formats like "MM/YYYY" or standard date formats
	 * @param string $date The date string to parse (e.g., "12/2025")
	 * @return \DateTime|null The parsed expiration date, or null if parsing fails
	 * @example getExpirationDateFromString('12/2025') returns DateTime for last day of December 2025
	 */
	public static function getExpirationDateFromString(string $date): ?\DateTime
	{
		$date = trim($date);
		if (str_contains($date, '/') && count($dateArr = explode('/', $date)) === 2 && !empty($year = $dateArr[1] ?? null) && !empty($month = $dateArr[0] ?? null)) {
			return self::getExpirationDateFromYearAndMonth((int)$year, (int)$month);
		}
		return Date::parse($date);
	}

	/**
	 * Create an expiration date from year and month
	 * The date is set to the last day of the specified month at 23:59:59
	 * Supports both 2-digit years (00-99) and 4-digit years (1900-2100)
	 * @param int $year The expiration year (e.g., 2025, or 25 for 2025)
	 * @param int $month The expiration month (1-12)
	 * @return \DateTime|null The expiration date (last day of the month), or null if invalid
	 * @example getExpirationDateFromYearAndMonth(2025, 12) returns DateTime for 2025-12-31 23:59:59
	 * @example getExpirationDateFromYearAndMonth(25, 12) returns DateTime for 2025-12-31 23:59:59
	 */
	public static function getExpirationDateFromYearAndMonth(int $year, int $month): ?\DateTime
	{
		if ($month < 1 || $month > 12) {
			return null;
		}

		// Support for 2-digit years (00-99 becomes 2000-2099)
		if ($year >= 0 && $year <= 99) {
			$year += 2000;
		}

		// Validate 4-digit year range
		if ($year < 1900 || $year > 2100) {
			return null;
		}

		try {
			return new \DateTime($year . '-' . $month . '-' .Date::getNumberOfDaysInMonth($year, $month).' 23:59:59');
		} catch (\Exception) {}
		return null;
	}

	/**
	 * Validate a card number using the Luhn algorithm
	 * Checks if the card number is valid for Visa, Mastercard, or American Express
	 * Automatically removes spaces and dashes from the input
	 * @param string $cardNumber The card number to validate
	 * @return bool True if the card number is valid, false otherwise
	 * @link https://en.wikipedia.org/wiki/Payment_card_number
	 * @example isValidCardNumber('4111111111111111') returns true (valid Visa)
	 * @example isValidCardNumber('4111-1111-1111-1111') returns true (spaces/dashes removed)
	 * @example isValidCardNumber('1234567890123456') returns false (invalid Luhn)
	 */
	public static function isValidCardNumber(string $cardNumber): bool
	{
		$cardNumber = trim(str_replace([' ', '-'], '', $cardNumber));

		if (empty($cardNumber) || preg_match('/^0+$/', $cardNumber)) {
			return false;
		}

		$constraint = new \Symfony\Component\Validator\Constraints\CardScheme(['VISA', 'MASTERCARD', 'AMEX']);
		return \Osimatic\Validator\Validator::getInstance()->validate($cardNumber, $constraint)->count() === 0;
	}

	/**
	 * Validate a card security code (CSC/CVV/CVC)
	 * Checks if the code is numeric and has the correct length (3-4 digits)
	 * @param string $csc The security code to validate
	 * @return bool True if the code is valid (numeric and 3-4 digits), false otherwise
	 * @example isValidCardCSC('123') returns true
	 * @example isValidCardCSC('1234') returns true
	 * @example isValidCardCSC('12') returns false (too short)
	 * @example isValidCardCSC('12a') returns false (not numeric)
	 */
	public static function isValidCardCSC(string $csc): bool
	{
		$csc = trim($csc);
		$length = strlen($csc);
		return $length >= 3 && $length <= 4 && ctype_digit($csc);
	}

	/**
	 * Format a card number with dashes based on its length
	 * Supports 16-digit cards (Visa, Mastercard) and 15-digit cards (American Express)
	 * Converts asterisks to the specified hidden character for masked numbers
	 * @param string $cardNumber The card number to format
	 * @param string $hiddenChar The character to use for masked digits (default: '*')
	 * @return string The formatted card number
	 * @example formatCardNumber('4111111111111111') returns "4111-1111-1111-1111" (Visa/MC format)
	 * @example formatCardNumber('378282246310005') returns "3782-822463-10005" (AMEX format)
	 * @example formatCardNumber('4111****11111111', 'X') returns "4111-XXXX-1111-1111" (masked with X)
	 * @example formatCardNumber('4111****11111111', '#') returns "4111-####-1111-1111" (masked with #)
	 */
	public static function formatCardNumber(string $cardNumber, string $hiddenChar = '*'): string
	{
		$cardNumber = trim(str_replace([' ', '-'], '', $cardNumber));
		$length = strlen($cardNumber);

		if ($length === 16) {
			// Visa, Mastercard, Discover: 4-4-4-4 format
			$cardNumber = substr($cardNumber, 0, 4).'-'.substr($cardNumber, 4, 4).'-'.substr($cardNumber, 8, 4).'-'.substr($cardNumber, 12, 4);
		} elseif ($length === 15) {
			// American Express: 4-6-5 format
			$cardNumber = substr($cardNumber, 0, 4).'-'.substr($cardNumber, 4, 6).'-'.substr($cardNumber, 10, 5);
		}

		return str_replace(['*', 'x', 'X'], $hiddenChar, $cardNumber);
	}

	/**
	 * Format a card expiration date for display
	 * @param \DateTime|null $expirationDate The expiration date to format
	 * @param int $dateFormatter The format style (default: IntlDateFormatter::LONG for "Month Year", or SHORT for "MM/yyyy")
	 * @return string|null The formatted expiration date, or null if date is null
	 * @example formatCardExpirationDate(new \DateTime('2025-12-31'), \IntlDateFormatter::LONG) returns "December 2025"
	 * @example formatCardExpirationDate(new \DateTime('2025-12-31'), \IntlDateFormatter::SHORT) returns "12/2025"
	 */
	public static function formatCardExpirationDate(?\DateTime $expirationDate, int $dateFormatter=\IntlDateFormatter::LONG): ?string
	{
		if (null === $expirationDate) {
			return null;
		}

		if (\IntlDateFormatter::LONG === $dateFormatter) {
			return Date::getMonthName((int) ($expirationDate->format('m'))).' '.$expirationDate->format('Y');
		}

		return \IntlDateFormatter::create(null, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, null, null, 'MM/yyyy')?->format($expirationDate);
	}

	/**
	 * Determine the card type from the card number
	 * Uses IIN (Issuer Identification Number) ranges to accurately identify the card brand
	 * Automatically removes spaces and dashes from the input
	 * @param string $cardNumber The card number to analyze
	 * @return BankCardType|null The detected card type, or null if not recognized
	 * @example getType('4111111111111111') returns BankCardType::VISA
	 * @example getType('5555555555554444') returns BankCardType::MASTER_CARD
	 * @example getType('378282246310005') returns BankCardType::AMERICAN_EXPRESS
	 */
	public static function getType(string $cardNumber): ?BankCardType
	{
		$cardNumber = trim(str_replace([' ', '-'], '', $cardNumber));

		if (empty($cardNumber)) {
			return null;
		}

		// Visa: starts with 4
		if ($cardNumber[0] === '4') {
			return BankCardType::VISA;
		}

		$firstTwo = (int)substr($cardNumber, 0, 2);
		$firstThree = (int)substr($cardNumber, 0, 3);
		$firstFour = (int)substr($cardNumber, 0, 4);

		// American Express: 34, 37
		if ($firstTwo === 34 || $firstTwo === 37) {
			return BankCardType::AMERICAN_EXPRESS;
		}

		// Mastercard: 51-55, 2221-2720
		if (($firstTwo >= 51 && $firstTwo <= 55) || ($firstFour >= 2221 && $firstFour <= 2720)) {
			return BankCardType::MASTER_CARD;
		}

		// Discover: 6011, 622126-622925, 644-649, 65
		if ($firstFour === 6011 || $firstTwo === 65 || ($firstThree >= 644 && $firstThree <= 649)) {
			if (strlen($cardNumber) >= 6) {
				$firstSix = (int)substr($cardNumber, 0, 6);
				if ($firstSix >= 622126 && $firstSix <= 622925) {
					return BankCardType::DISCOVER_NETWORK;
				}
			}
			return BankCardType::DISCOVER_NETWORK;
		}

		// Diners Club: 300-305, 36, 38
		if (($firstThree >= 300 && $firstThree <= 305) || $firstTwo === 36 || $firstTwo === 38) {
			return BankCardType::DINNER_CLUB;
		}

		return null;
	}

	// ========================================
	// DEPRECATED METHODS (Backward Compatibility)
	// ========================================

	/**
	 * @deprecated Use isValidCardNumber() instead
	 * @param string $cardNumber The card number to validate
	 * @return bool True if the card number is valid, false otherwise
	 */
	public static function checkCardNumber(string $cardNumber): bool
	{
		return self::isValidCardNumber($cardNumber);
	}

	/**
	 * @deprecated Use isValidCardCSC() instead
	 * @param string $csc The security code to validate
	 * @return bool True if the code is valid, false otherwise
	 */
	public static function checkCardCSC(string $csc): bool
	{
		return self::isValidCardCSC($csc);
	}
}