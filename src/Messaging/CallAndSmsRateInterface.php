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
	 * Get the landline call rate per minute in the currency specified by getCurrency().
	 * @return float The rate per minute (e.g., 0.085 for 0.085 EUR/minute)
	 */
	public function getLandlineRatePerMinute(): float;

	/**
	 * Set the landline call rate per minute in the currency specified by getCurrency().
	 * @param float $landlineRatePerMinute The rate per minute (e.g., 0.085 for 0.085 EUR/minute)
	 */
	public function setLandlineRatePerMinute(float $landlineRatePerMinute): void;

	/**
	 * Get the mobile call rate per minute in the currency specified by getCurrency().
	 * @return float The rate per minute (e.g., 0.12 for 0.12 EUR/minute)
	 */
	public function getMobileRatePerMinute(): float;

	/**
	 * Set the mobile call rate per minute in the currency specified by getCurrency().
	 * @param float $mobileRatePerMinute The rate per minute (e.g., 0.12 for 0.12 EUR/minute)
	 */
	public function setMobileRatePerMinute(float $mobileRatePerMinute): void;

	/**
	 * Get the SMS rate in the currency specified by getCurrency().
	 * @return float The rate per SMS (e.g., 0.085 for 0.085 EUR/SMS)
	 */
	public function getSmsRate(): float;

	/**
	 * Set the SMS rate in the currency specified by getCurrency().
	 * @param float $smsRate The rate per SMS (e.g., 0.085 for 0.085 EUR/SMS)
	 */
	public function setSmsRate(float $smsRate): void;
}