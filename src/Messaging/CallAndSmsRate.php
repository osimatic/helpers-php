<?php

namespace Osimatic\Messaging;

/**
 * Utility class for calculating SMS and call costs based on phone numbers and configured rates.
 * This class provides static methods to retrieve pricing information for SMS and calls to different countries and phone number types.
 */
class CallAndSmsRate
{
	/**
	 * Array of call and SMS rate configurations indexed by country.
	 * @var CallAndSmsRateInterface[]
	 */
	private static array $callAndSmsRates = [];

	/**
	 * Get the SMS cost for sending to a phone number (in cents).
	 * @param string $phoneNumber The destination phone number
	 * @return float The SMS cost in cents, or 0 if no rate is configured for the country
	 */
	public static function getSmsCostByPhoneNumber(string $phoneNumber): float
	{
		if (null !== ($callAndSmsRate = self::getRatesOfCountry(PhoneNumber::getCountryIsoCode($phoneNumber)))) {
			return $callAndSmsRate->getSmsRate();
		}
		return 0;
	}

	/**
	 * Calculate the call cost for a phone number and duration (in cents).
	 * @param string $phoneNumber The destination phone number
	 * @param int $durationInSeconds The call duration in seconds
	 * @return float The total call cost in cents, or 0 if no rate is configured for the country
	 */
	public static function getCallCostByPhoneNumberAndDuration(string $phoneNumber, int $durationInSeconds): float
	{
		$ratePerMinute = self::getCallRatePerMinuteByPhoneNumber($phoneNumber);
		return round(($durationInSeconds/60) * $ratePerMinute, 3);
	}

	/**
	 * Get the call rate per minute for a phone number (in cents/minute).
	 * The rate varies depending on whether the number is mobile or landline.
	 * @param string $phoneNumber The destination phone number
	 * @return float The call rate per minute in cents, or 0 if no rate is configured for the country
	 */
	public static function getCallRatePerMinuteByPhoneNumber(string $phoneNumber): float
	{
		if (null !== ($callAndSmsRate = self::getRatesOfCountry(PhoneNumber::getCountryIsoCode($phoneNumber)))) {
			if (PhoneNumber::isMobile($phoneNumber)) {
				return $callAndSmsRate->getMobileRatePerMinute();
			}
			return $callAndSmsRate->getLandlineRatePerMinute();
		}
		return 0;
	}

	/**
	 * Get the rate configuration for a specific country.
	 * @param string|null $countryIsoCode The ISO country code (e.g., 'FR', 'US')
	 * @return CallAndSmsRateInterface|null The rate configuration for the country, or null if not found
	 */
	public static function getRatesOfCountry(?string $countryIsoCode): ?CallAndSmsRateInterface
	{
		if (empty($countryIsoCode)) {
			return null;
		}

		foreach (self::$callAndSmsRates as $callAndSmsRate) {
			if ($callAndSmsRate->getCountryCode() === $countryIsoCode) {
				return $callAndSmsRate;
			}
		}
		return null;
	}

	/**
	 * Set the call and SMS rate configurations for all countries.
	 * @param CallAndSmsRateInterface[] $callAndSmsRates Array of rate configuration objects
	 */
	public static function setRates(array $callAndSmsRates): void
	{
		self::$callAndSmsRates = $callAndSmsRates;
	}
}