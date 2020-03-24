<?php

namespace Osimatic\Helpers;

class PhoneNumber
{

	public static function formatNational(string $phoneNumber, string $defaultCountry='FR'): string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::NATIONAL, $defaultCountry);
	}

	public static function formatInternational(string $phoneNumber, string $defaultCountry='FR'): string
	{
		return self::format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL, $defaultCountry);
	}

	public static function format(string $phoneNumber, $numberFormat, string $defaultCountry='FR'): string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, self::LOCALE);
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
	public static function parse(string $phoneNumber, string $defaultCountry='FR'): string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->format($phoneNumberObj, \libphonenumber\PhoneNumberFormat::E164);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return $phoneNumber;
	}

	public static function isMobile(string $phoneNumber, string $defaultCountry='FR'): bool
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			return \libphonenumber\PhoneNumberUtil::getInstance()->getNumberType($phoneNumberObj) == 1;
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return false;
	}

	public static function getCountryIsoCode(string $phoneNumber, string $defaultCountry='FR'): ?string
	{
		try {
			$phoneNumberObj = \libphonenumber\PhoneNumberUtil::getInstance()->parse($phoneNumber, $defaultCountry);
			if ($phoneNumberObj->getCountryCode() == 44) {
				return 'UK'; // bug pour les numéros anglais...
			}
			return \libphonenumber\PhoneNumberUtil::getInstance()->getRegionCodeForNumber($phoneNumberObj);
		}
		catch (\libphonenumber\NumberParseException $e) { }
		return null;
	}

}