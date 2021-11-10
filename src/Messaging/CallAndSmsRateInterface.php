<?php

namespace Osimatic\Helpers\Messaging;

interface CallAndSmsRateInterface
{
	/**
	 * @return int
	 */
	public function getContinent(): int;

	/**
	 * @param int $continent
	 */
	public function setContinent(int $continent): void;

	/**
	 * @return string
	 */
	public function getCountryCode(): string;

	/**
	 * @param string $countryCode
	 */
	public function setCountryCode(string $countryCode): void;

	/**
	 * @return string
	 */
	public function getCurrency(): string;

	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency): void;

	/**
	 * en centimes
	 * @return int
	 */
	public function getLandlineRatePerMinute(): int;

	/**
	 * en centimes
	 * @param int $landlineRatePerMinute
	 */
	public function setLandlineRatePerMinute(int $landlineRatePerMinute): void;

	/**
	 * en centimes
	 * @return int
	 */
	public function getMobileRatePerMinute(): int;

	/**
	 * en centimes
	 * @param int $mobileRatePerMinute
	 */
	public function setMobileRatePerMinute(int $mobileRatePerMinute): void;

	/**
	 * en centimes
	 * @return int
	 */
	public function getSmsRate(): int;

	/**
	 * en centimes
	 * @param int $smsRate
	 */
	public function setSmsRate(int $smsRate): void;
}