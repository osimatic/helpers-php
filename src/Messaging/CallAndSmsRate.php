<?php

namespace Osimatic\Helpers\Messaging;

class CallAndSmsRate
{
	/**
	 * @var CallAndSmsRateInterface[]
	 */
	private static array $callAndSmsRates = [];

	/**
	 * Retour le tarif en centimes
	 * @param string $phoneNumber
	 * @return int
	 */
	public static function getSmsCostByPhoneNumber(string $phoneNumber): int
	{
		if (null !== ($callAndSmsRate = self::getRatesOfCountry(\Osimatic\Helpers\Messaging\PhoneNumber::getCountryIsoCode($phoneNumber)))) {
			return $callAndSmsRate->getSmsRate();
		}
		return 0;
	}

	/**
	 * Retour le tarif en centimes / minute
	 * @param string $phoneNumber
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getCallCostByPhoneNumberAndDuration(string $phoneNumber, int $durationInSeconds): int
	{
		$ratePerMinute = self::getCallRatePerMinuteByPhoneNumber($phoneNumber);
		return (int) round(($durationInSeconds/60) * $ratePerMinute);
	}

	/**
	 * Retour le tarif en centimes / minute
	 * @param string $phoneNumber
	 * @return int
	 */
	public static function getCallRatePerMinuteByPhoneNumber(string $phoneNumber): int
	{
		if (null !== ($callAndSmsRate = self::getRatesOfCountry(\Osimatic\Helpers\Messaging\PhoneNumber::getCountryIsoCode($phoneNumber)))) {
			if (\Osimatic\Helpers\Messaging\PhoneNumber::isMobile($phoneNumber)) {
				return $callAndSmsRate->getMobileRatePerMinute();
			}
			return $callAndSmsRate->getLandlineRatePerMinute();
		}
		return 0;
	}

	/**
	 * @param string $countryIsoCode
	 * @return CallAndSmsRateInterface|null
	 */
	public static function getRatesOfCountry(string $countryIsoCode): ?CallAndSmsRateInterface
	{
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