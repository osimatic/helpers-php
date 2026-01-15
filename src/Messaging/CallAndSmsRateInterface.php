<?php

namespace Osimatic\Messaging;

use Osimatic\Location\Continent;

/**
 * Interface for call and SMS rate information.
 * This interface must be implemented by any class that stores pricing information for calls and SMS messages by country.
 */
interface CallAndSmsRateInterface
{
	/**
	 * Get the continent.
	 * @return Continent The continent for these rates
	 */
	public function getContinent(): Continent;

	/**
	 * Set the continent.
	 * @param Continent $continent The continent to set
	 */
	public function setContinent(Continent $continent): void;

	/**
	 * Get the country code.
	 * @return string The ISO country code
	 */
	public function getCountryCode(): string;

	/**
	 * Set the country code.
	 * @param string $countryCode The ISO country code to set
	 */
	public function setCountryCode(string $countryCode): void;

	/**
	 * Get the currency.
	 * @return string The ISO currency code
	 */
	public function getCurrency(): string;

	/**
	 * Set the currency.
	 * @param string $currency The ISO currency code to set
	 */
	public function setCurrency(string $currency): void;

	/**
	 * Get the landline call rate per minute (in cents).
	 * @return float The rate per minute in cents
	 */
	public function getLandlineRatePerMinute(): float;

	/**
	 * Set the landline call rate per minute (in cents).
	 * @param float $landlineRatePerMinute The rate per minute in cents
	 */
	public function setLandlineRatePerMinute(float $landlineRatePerMinute): void;

	/**
	 * Get the mobile call rate per minute (in cents).
	 * @return float The rate per minute in cents
	 */
	public function getMobileRatePerMinute(): float;

	/**
	 * Set the mobile call rate per minute (in cents).
	 * @param float $mobileRatePerMinute The rate per minute in cents
	 */
	public function setMobileRatePerMinute(float $mobileRatePerMinute): void;

	/**
	 * Get the SMS rate (in cents).
	 * @return float The rate per SMS in cents
	 */
	public function getSmsRate(): float;

	/**
	 * Set the SMS rate (in cents).
	 * @param float $smsRate The rate per SMS in cents
	 */
	public function setSmsRate(float $smsRate): void;
}