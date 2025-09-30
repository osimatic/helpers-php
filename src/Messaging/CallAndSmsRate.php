<?php

namespace Osimatic\Messaging;

class CallAndSmsRate
{
	/**
	 * @var CallAndSmsRateInterface[]
	 */
	private static array $callAndSmsRates = [];

	/**
	 * Retour le tarif en centimes
	 * @param string $phoneNumber
	 * @return float
	 */
	public static function getSmsCostByPhoneNumber(string $phoneNumber): float
	{
		if (null !== ($callAndSmsRate = self::getRatesOfCountry(PhoneNumber::getCountryIsoCode($phoneNumber)))) {
			return $callAndSmsRate->getSmsRate();
		}
		return 0;
	}

	/**
	 * Retour le tarif en centimes / minute
	 * @param string $phoneNumber
	 * @param int $durationInSeconds
	 * @return float
	 */
	public static function getCallCostByPhoneNumberAndDuration(string $phoneNumber, int $durationInSeconds): float
	{
		$ratePerMinute = self::getCallRatePerMinuteByPhoneNumber($phoneNumber);
		return round(($durationInSeconds/60) * $ratePerMinute, 3);
	}

	/**
	 * Retour le tarif en centimes / minute
	 * @param string $phoneNumber
	 * @return float
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
	 * @param string|null $countryIsoCode
	 * @return CallAndSmsRateInterface|null
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
	 * @param CallAndSmsRateInterface[] $callAndSmsRates
	 */
	public static function setRates(array $callAndSmsRates): void
	{
		self::$callAndSmsRates = $callAndSmsRates;
	}
}