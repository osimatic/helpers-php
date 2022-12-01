<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\Location\Continent;

interface CallAndSmsRateInterface
{
	/**
	 * @return Continent
	 */
	public function getContinent(): Continent;

	/**
	 * @param Continent $continent
	 */
	public function setContinent(Continent $continent): void;

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
	 * @return float
	 */
	public function getLandlineRatePerMinute(): float;

	/**
	 * en centimes
	 * @param float $landlineRatePerMinute
	 */
	public function setLandlineRatePerMinute(float $landlineRatePerMinute): void;

	/**
	 * en centimes
	 * @return float
	 */
	public function getMobileRatePerMinute(): float;

	/**
	 * en centimes
	 * @param float $mobileRatePerMinute
	 */
	public function setMobileRatePerMinute(float $mobileRatePerMinute): void;

	/**
	 * en centimes
	 * @return float
	 */
	public function getSmsRate(): float;

	/**
	 * en centimes
	 * @param float $smsRate
	 */
	public function setSmsRate(float $smsRate): void;
}