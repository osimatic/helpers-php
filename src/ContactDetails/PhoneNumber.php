<?php

namespace Osimatic\Helpers\ContactDetails;

class PhoneNumber
{

	/**
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function formatNational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::NATIONAL, $defaultCountry);
	}

	/**
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function formatInternational(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL, $defaultCountry);
	}

	/**
	 * @param string $phoneNumber
	 * @param int $numberFormat
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function format(?string $phoneNumber, int $numberFormat, string $defaultCountry='FR'): ?string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, $numberFormat);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return $phoneNumber;
	}

	/**
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return string
	 */
	public static function parse(?string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, \libphonenumber\PhoneNumberFormat::E164);
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
	 * @param string $phoneNumber
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
	 * @param string $phoneNumber
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
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return null|int
	 */
	public static function getType(?string $phoneNumber, string $defaultCountry='FR'): ?int
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->getNumberType($phoneNumberObj);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return null;
	}

	/**
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isMobile(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) == libphonenumber\PhoneNumberType::MOBILE;
	}

	/**
	 * @param string $phoneNumber
	 * @param string $defaultCountry
	 * @return bool
	 */
	public static function isPremium(?string $phoneNumber, string $defaultCountry='FR'): bool
	{
		return self::getType($phoneNumber, $defaultCountry) == libphonenumber\PhoneNumberType::PREMIUM_RATE;
	}

	/**
	 * @param string $phoneNumber
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

	public static function formatFromIvr(?string $phoneNumber): ?string
	{
		if (substr($phoneNumber, 0, 1) != '0') {
			if (strlen($phoneNumber) > 9) {
				return '00'.$phoneNumber;
			}
			if (strlen($phoneNumber) == 9) {
				return '0' . $phoneNumber;
			}
		}
		return $phoneNumber;
	}

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