<?php

namespace Osimatic\Network;

/**
 * Class DNS
 * Provides utilities for domain name validation and parsing
 */
class DNS
{

	// ========== Validation ==========

	/**
	 * Checks the syntax of a domain name
	 * @param string $dns the domain name to check
	 * @return boolean true if the domain name is syntactically correct, false otherwise
	 */
	public static function isValid(string $dns): bool
	{
		//return filter_var($url, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

		return preg_match('/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', $dns) === 1;

		/*
		return \Osimatic\Validator\Validator::getInstance()->validate($dns, new \Symfony\Component\Validator\Constraints\Hostname())->count() === 0;
		*/
	}

	// ========== Components of DNS ==========

	/**
	 * Returns the top-level domain from a domain name, optionally with the "." separator
	 * @param string $dns the domain name from which to retrieve the top-level domain
	 * @param boolean $withPoint true to add the "." separator before the top-level domain, false otherwise (true by default)
	 * @return string the top-level domain from the domain name
	 * @link https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
	 */
	public static function getTld(string $dns, bool $withPoint=true): string
	{
		$tld = substr($dns, strrpos($dns, '.'));

		if (!$withPoint) {
			$tld = substr($tld, 1);
		}

		return $tld;
		//return substr($host, strrpos($host, '.')+1);
	}

	/**
	 * Alias of the DNS::getTld() function
	 * @param string $dns the domain name
	 * @return string the top-level domain
	 */
	public static function getTopLevelDomain(string $dns): string
	{
		return self::getTld($dns);
	}

	/**
	 * Returns the second-level domain from a domain name, optionally with the top-level domain
	 * @param string $dns the domain name from which to retrieve the second-level domain
	 * @param boolean $withTld true to add the top-level domain from the domain name, false otherwise (true by default)
	 * @return string the second-level domain from the domain name
	 */
	public static function getSld(string $dns, bool $withTld=true): string
	{
		$hostWithoutTld = substr($dns, 0, strrpos($dns, '.'));
		if (false !== ($lastDotPos = strrpos($hostWithoutTld, '.'))) {
			$sld = substr($hostWithoutTld, $lastDotPos + 1);
		}
		else {
			$sld = $hostWithoutTld;
		}

		if ($withTld) {
			$sld .= self::getTld($dns);
		}

		return $sld;
	}

	/**
	 * Alias of the DNS::getSld() function
	 * @param string $dns the domain name
	 * @param bool $withTld true to include the top-level domain, false otherwise
	 * @return string the second-level domain
	 */
	public static function getSecondLevelDomain(string $dns, bool $withTld=true): string
	{
		return self::getSld($dns, $withTld);
	}

	// ========== DEPRECATED METHODS (Backward Compatibility) ==========

	/**
	 * @deprecated Use isValid() instead
	 */
	public static function check(string $dns): bool
	{
		return self::isValid($dns);
	}

}