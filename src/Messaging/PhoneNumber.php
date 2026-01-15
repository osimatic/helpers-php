<?php

namespace Osimatic\Messaging;

/**
 * Utility class for phone number validation, parsing, and formatting.
 * This class wraps the libphonenumber library to provide convenient static methods for working with phone numbers.
 */
class PhoneNumber
{
	/**
	 * Format a phone number in national format (e.g., "01 23 45 67 89" for France).
	 * @param string|null $phoneNumber The phone number to format
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return string|null The formatted phone number, or the original value if parsing fails, or null if input is null
	 */
	public static function formatNational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::NATIONAL, $defaultCountry);
	}

	/**
	 * Format a phone number in international format (e.g., "+33 1 23 45 67 89").
	 * @param string|null $phoneNumber The phone number to format
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return string|null The formatted phone number, or the original value if parsing fails, or null if input is null
	 */
	public static function formatInternational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL, $defaultCountry);
	}

	/**
	 * Format a phone number using a specific format.
	 * @param string|null $phoneNumber The phone number to format
	 * @param int $numberFormat The libphonenumber format constant (NATIONAL, INTERNATIONAL, E164, etc.)
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return string|null The formatted phone number, or the original value if parsing fails, or null if input is null
	 */
	public static function format(?string $phoneNumber, int $numberFormat, string $defaultCountry='FR'): ?string
	{
		if (null === $phoneNumber) {
			return null;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, $numberFormat);
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return $phoneNumber;
	}

	/**
	 * Parse a phone number and return it in E.164 format (e.g., "+33123456789").
	 * @param string|null $phoneNumber The phone number to parse
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return string|null The parsed phone number in E.164 format, or the original value if parsing fails, or null if input is null
	 */
	public static function parse(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		if (null === $phoneNumber) {
			return null;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, \libphonenumber\PhoneNumberFormat::E164);
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return $phoneNumber;
	}

	/**
	 * Parse an array of phone numbers and return them in E.164 format.
	 * @param string[] $phoneNumbers Array of phone numbers to parse
	 * @param string $defaultCountry The ISO country code to use if phone numbers don't include a country code (default: 'FR')
	 * @return string[] Array of parsed phone numbers in E.164 format, with invalid entries filtered out
	 */
	public static function parseList(array $phoneNumbers, string $defaultCountry='FR'): array
	{
		foreach ($phoneNumbers as $key => $phoneNumber) {
			if (!empty($phoneNumber)) {
				$phoneNumbers[$key] = self::parse($phoneNumber, $defaultCountry);
			}
		}
		return array_filter($phoneNumbers);
	}

	/**
	 * Quickly check if a number is a possible phone number using only length information.
	 * This is much faster than full validation but less accurate.
	 * @param string|null $phoneNumber The phone number to check
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the number is possibly valid based on length, false otherwise
	 */
	public static function isPossible(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		if (null === $phoneNumber) {
			return false;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->isPossibleNumber($phoneNumberObj);
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return false;
	}

	/**
	 * Perform full validation of a phone number using length and prefix information.
	 * @param string|null $phoneNumber The phone number to validate
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the phone number is valid, false otherwise
	 */
	public static function isValid(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		if (null === $phoneNumber) {
			return false;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->isValidNumber($phoneNumberObj);
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return false;
	}

	/**
	 * Get the type of phone number (mobile, fixed-line, toll-free, premium rate, etc.).
	 * @param string|null $phoneNumber The phone number to analyze
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return PhoneNumberType|null The phone number type, or null if it cannot be determined
	 */
	public static function getType(?string $phoneNumber, string $defaultCountry='FR'): ?PhoneNumberType
	{
		if (null === $phoneNumber) {
			return null;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return PhoneNumberType::tryFrom(\libphonenumber\PhoneNumberUtil::getInstance()->getNumberType($phoneNumberObj));
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return null;
	}

	/**
	 * Check if a phone number is a mobile number.
	 * @param string|null $phoneNumber The phone number to check
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the number is a mobile number, false otherwise
	 */
	public static function isMobile(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) === PhoneNumberType::MOBILE;
	}

	/**
	 * Check if a phone number is a fixed-line (landline) number.
	 * @param string|null $phoneNumber The phone number to check
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the number is a fixed-line number, false otherwise
	 */
	public static function isFixedLine(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) === PhoneNumberType::FIXED_LINE;
	}

	/**
	 * Check if a phone number is a premium rate number.
	 * @param string|null $phoneNumber The phone number to check
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the number is a premium rate number, false otherwise
	 */
	public static function isPremium(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) === PhoneNumberType::PREMIUM_RATE;
	}

	/**
	 * Check if a phone number is a toll-free number.
	 * @param string|null $phoneNumber The phone number to check
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return bool True if the number is a toll-free number, false otherwise
	 */
	public static function isTollFree(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) === PhoneNumberType::TOLL_FREE;
	}

	/**
	 * Get the ISO country code for a phone number.
	 * @param string|null $phoneNumber The phone number to analyze
	 * @param string $defaultCountry The ISO country code to use if the phone number doesn't include a country code (default: 'FR')
	 * @return string|null The ISO country code (e.g., 'FR', 'US'), or null if it cannot be determined
	 */
	public static function getCountryIsoCode(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		if (null === $phoneNumber) {
			return null;
		}

		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->getRegionCodeForNumber($phoneNumberObj);
			}
		}
		catch (\libphonenumber\NumberParseException) {}
		return null;
	}

	/**
	 * Format a phone number from IVR system format to standard format.
	 * Handles special cases like anonymous numbers and French overseas territories.
	 * @param string|null $phoneNumber The phone number from the IVR system
	 * @return string|null The formatted phone number, or empty string for anonymous numbers, or null if input is null
	 */
	public static function formatFromIvr(?string $phoneNumber): ?string
	{
		if (null === $phoneNumber) {
			return null;
		}

		if ('' === $phoneNumber || '0' === $phoneNumber || 'Anonymous' === $phoneNumber) {
			return '';
		}

		if (!str_starts_with($phoneNumber, '+33') && !str_starts_with($phoneNumber, '0')) {
			if (mb_strlen($phoneNumber) > 9) {
				return '00'.$phoneNumber;
			}
			if (mb_strlen($phoneNumber) === 9) {
				return '0' . $phoneNumber;
			}
		}

		// Handle French overseas territories numbers with '0' instead of '00'
		$frenchOverseasCallingCodes = [
			'262', // Reunion / Mayotte
			'508', // Saint Pierre and Miquelon
			'590', // Guadeloupe
			'596', // Martinique
			'594', // French Guiana
			'687', // New Caledonia
			'689', // French Polynesia
			'681', // Wallis and Futuna
		];

		foreach ($frenchOverseasCallingCodes as $callingCode) {
			if (!str_starts_with($phoneNumber, '+33') && substr($phoneNumber, 0, 7) === '0'.$callingCode.$callingCode && mb_strlen($phoneNumber) === 13) {
				$phoneNumber = '+'.substr($phoneNumber, 1);
			}
		}

		return $phoneNumber;
	}

	/**
	 * Format a phone number for IVR system usage.
	 * Converts international format to a format compatible with the IVR system.
	 * @param string|null $phoneNumber The phone number to format
	 * @param bool $withTrunkCode Whether to include the trunk code (leading '0') for national numbers (default: true)
	 * @return string|null The formatted phone number for IVR, or null if input is null
	 */
	public static function formatForIvr(?string $phoneNumber, bool $withTrunkCode=true): ?string
	{
		if (null === $phoneNumber) {
			return null;
		}

		// Temporary code because the IVR system cannot dial numbers starting with 0033
		if (str_starts_with($phoneNumber, '+')) {
			$phoneNumber = '00'.substr($phoneNumber, 1);
		}
		if (str_starts_with($phoneNumber, '0033')) {
			$phoneNumber = substr($phoneNumber, 4);
			if ($withTrunkCode && mb_strlen($phoneNumber) > 5) {
				$phoneNumber = '0'.$phoneNumber;
			}
		}
		return $phoneNumber;
	}
}