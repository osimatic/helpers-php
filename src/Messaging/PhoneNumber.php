<?php

namespace Osimatic\Helpers\Messaging;

/**
 * Class PhoneNumber
 * @package Osimatic\Helpers\Messaging
 */
class PhoneNumber
{

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function formatNational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::NATIONAL, $defaultCountry);
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function formatInternational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL, $defaultCountry);
	}

	/**
	 * @param string|null $phoneNumber
	 * @param int $numberFormat
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function format(?string $phoneNumber, int $numberFormat, string $defaultCountry='FR'): ?string
	{
		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, $numberFormat);
			}
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return $phoneNumber;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function parse(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, \libphonenumber\PhoneNumberFormat::E164);
			}
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return $phoneNumber;
	}

	/**
	 * @param string[] $phoneNumbers
	 * @param string $defaultCountry
	 * @return string[]
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
	 * quickly guesses whether a number is a possible phone number by using only the length information, much faster than a full validation.
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isPossible(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->isValidNumber($phoneNumberObj);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return false;
	}

	/**
	 * full validation of a phone number for a region using length and prefix information.
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isValid(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->isValidNumber($phoneNumberObj);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return false;
	}

	/**
	 * gets the type of the number based on the number itself; able to distinguish Fixed-line, Mobile, Toll-free, Premium Rate, Shared Cost, VoIP, Personal Numbers, UAN, Pager, and Voicemail (whenever feasible).
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return null|int
	 */
	public static function getType(?string $phoneNumber, string $defaultCountry='FR'): ?int
	{
		try {
			if (null !== ($phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry))) {
				return \libphonenumber\PhoneNumberUtil::getInstance()->getNumberType($phoneNumberObj);
			}
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return null;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isMobile(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) == \libphonenumber\PhoneNumberType::MOBILE;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isFixedLine(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) == \libphonenumber\PhoneNumberType::FIXED_LINE;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isPremium(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) == \libphonenumber\PhoneNumberType::PREMIUM_RATE;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param string $defaultCountry
	 * @return string|null
	 */
	public static function getCountryIsoCode(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			//if ($phoneNumberObj->getCountryCode() == 44) {
			//	return 'UK'; // bug pour les numéros anglais...
			//}
			return \libphonenumber\PhoneNumberUtil::getInstance()->getRegionCodeForNumber($phoneNumberObj);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return null;
	}

	/**
	 * @param string|null $phoneNumber
	 * @return string|null
	 */
	public static function formatFromIvr(?string $phoneNumber): ?string
	{
		if (substr($phoneNumber, 0, 3) != '+33') {
			if (substr($phoneNumber, 0, 1) != '0') {
				if (strlen($phoneNumber) > 9) {
					return '00'.$phoneNumber;
				}
				if (strlen($phoneNumber) == 9) {
					return '0' . $phoneNumber;
				}
			}
		}

		// Cas numéro France DOM-TOM avec un 0 à la place du 00
		$frenchOverseasCallingCodes = [
			'262', // La Réunion / Mayotte
			'508', // Saint-Pierre-et-Miquelon
			'590', // Guadeloupe
			'596', // Martinique
			'594', // Guyane
			'687', // Nouvelle Calédonie
			'689', // Polynésie Française
			'681', // Wallis-et-Futuna
		];
		
		foreach ($frenchOverseasCallingCodes as $callingCode) {
			if (substr($phoneNumber, 0, 3) != '+33') {
				if (substr($phoneNumber, 0, 7) === '0'.$callingCode.$callingCode && strlen($phoneNumber) === 13) { // Guadeloupe
					$phoneNumber = '+'.substr($phoneNumber, 1);
				}
			}
		}

		return $phoneNumber;
	}

	/**
	 * @param string|null $phoneNumber
	 * @param bool $withTrunkCode
	 * @return string|null
	 */
	public static function formatForIvr(?string $phoneNumber, bool $withTrunkCode=true): ?string
	{
		// code provisoire car le svi ne sait pas appeler des numéros commencant par 0033
		if (substr($phoneNumber, 0, 1) == '+') {
			$phoneNumber = '00'.substr($phoneNumber, 1);
		}
		if (substr($phoneNumber, 0, 4) == '0033') {
			$phoneNumber = substr($phoneNumber, 4);
			if ($withTrunkCode && strlen($phoneNumber) > 5) {
				$phoneNumber = '0'.$phoneNumber;
			}
		}
		return $phoneNumber;
	}

}