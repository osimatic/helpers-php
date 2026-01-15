<?php

namespace Osimatic\Messaging;

/**
 * Utility class for email address validation and parsing.
 * This class provides static methods for working with email addresses, including validation and extraction of host and TLD information.
 */
class EmailAddress
{

	// ========== Validation ==========

	/**
	 * Check if an email address is valid using PHP's built-in email validation.
	 * @param string $email The email address to validate
	 * @return bool True if the email address is valid, false otherwise
	 */
	public static function check(string $email): bool
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	// ========== Get element ==========

	/**
	 * Get the host (domain) part of an email address.
	 * @param string $email The email address to extract the host from
	 * @return string|null The host/domain part of the email address (e.g., "example.com"), or null if no @ symbol is found
	 */
	public static function getHost(string $email): ?string
	{
		if (!str_contains($email, '@')) {
			return null;
		}
		return substr($email, (strpos($email, '@')+1));
	}

	/**
	 * Get the top-level domain (TLD) from an email address.
	 * @param string $email The email address to extract the TLD from
	 * @param boolean $withPoint Whether to include the dot separator before the TLD (default: true)
	 * @return string|null The TLD (e.g., ".com" or "com"), or null if it cannot be extracted
	 */
	public static function getTld(string $email, bool $withPoint=true): ?string
	{
		if (null === ($host = self::getHost($email))) {
			return null;
		}
		return \Osimatic\Network\DNS::getTld($host, $withPoint);
	}



}