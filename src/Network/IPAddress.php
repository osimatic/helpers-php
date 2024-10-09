<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class IPAddress
{
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	// ========== Vérification ==========

	/**
	 * Vérifie la syntaxe d'une adresse IP V4
	 * @param string $adresseIp l'adresse IP à vérifier
	 * @return boolean true si l'adresse IP est syntaxiquement correcte, false sinon
	 */
	public static function check(string $adresseIp): bool
	{
		return self::checkIpV4($adresseIp);
	}

	/**
	 * Vérifie la syntaxe d'une adresse IP V4
	 * @param string $adresseIp l'adresse IP à vérifier
	 * @return boolean true si l'adresse IP est syntaxiquement correcte, false sinon
	 */
	public static function checkIpV4(string $adresseIp): bool
	{
		return filter_var($adresseIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	/**
	 * Vérifie la syntaxe d'une adresse IP V6
	 * @param string $adresseIp l'adresse IP à vérifier
	 * @return boolean true si l'adresse IP est syntaxiquement correcte, false sinon
	 */
	public static function checkIpV6(string $adresseIp): bool
	{
		return filter_var($adresseIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	/**
	 * Vérifie la syntaxe d'une page d'adresse IP V4
	 * @param string $ipAddressRange
	 * @param string $rangeSeparator
	 * @return bool
	 */
	public static function checkRange(string $ipAddressRange, string $rangeSeparator='-'): bool
	{
		if (!str_contains($ipAddressRange, $rangeSeparator)) {
			return false;
		}

		[$rangeStartIp, $rangeEndIp] = explode($rangeSeparator, $ipAddressRange);
		return self::checkIpV4(trim($rangeStartIp)) && self::checkIpV4(trim($rangeEndIp));
	}

	/**
	 * Vérifie la syntaxe d'une page d'adresse IP V6
	 * @param string $ipAddressRange
	 * @param string $rangeSeparator
	 * @return bool
	 */
	public static function checkRangeOfIpV6(string $ipAddressRange, string $rangeSeparator='-'): bool
	{
		if (!str_contains($ipAddressRange, $rangeSeparator)) {
			return false;
		}

		[$rangeStartIp, $rangeEndIp] = explode($rangeSeparator, $ipAddressRange);
		return self::checkIpV6(trim($rangeStartIp)) && self::checkIpV6(trim($rangeEndIp));
	}

	// ========== Infos sur adresses IP ==========

	/**
	 * @param string $ipAddress
	 * @return bool
	 */
	public function isVpn(string $ipAddress): bool
	{
		$api = new \Osimatic\Network\Incolumitas($this->logger);
		$result = $api->getIpInfos($ipAddress);
		return \Osimatic\Network\Incolumitas::isVpn($result);
	}

	// ========== Plages d'adresses IP ==========

	/**
	 * @param string $ipAddress
	 * @param string $ipAddressRange
	 * @param string $rangeSeparator
	 * @return bool
	 */
	public static function isInRangeOfIpAddressRange(string $ipAddress, string $ipAddressRange, string $rangeSeparator='-'): bool
	{
		if (!str_contains($ipAddressRange, $rangeSeparator)) {
			return false;
		}
		[$rangeStartIp, $rangeEndIp] = explode($rangeSeparator, $ipAddressRange);
		return self::isInRangeOfIpAddresses($ipAddress, $rangeStartIp, $rangeEndIp);
	}

	/**
	 * @param string $ipAddress
	 * @param string $ipAddressRangeBegin
	 * @param string $ipAddressRangeEnd
	 * @return bool
	 */
	public static function isInRangeOfIpAddresses(string $ipAddress, string $ipAddressRangeBegin, string $ipAddressRangeEnd): bool
	{
		$ipAddressLongFormatRangeBegin = ip2long($ipAddressRangeBegin);
		$ipAddressLongFormatRangeEnd = ip2long($ipAddressRangeEnd);
		$ipAddressLongFormat = ip2long($ipAddress);
		return (($ipAddressLongFormatRangeBegin <= $ipAddressLongFormat) && ($ipAddressLongFormatRangeEnd >= $ipAddressLongFormat));
	}

	/**
	 * @param string $ipAddress
	 * @param string $ipAddressCompare
	 * @return bool
	 */
	public static function correspondToIpAddress(string $ipAddress, string $ipAddressCompare): bool
	{
		if ($ipAddress === $ipAddressCompare) {
			return true;
		}

		if (str_ends_with($ipAddressCompare, '%')) {
			$debutAdresseIp = substr($ipAddressCompare, 0, -1);
			if (str_starts_with($ipAddress, $debutAdresseIp)) {
				return true;
			}
		}

		return false;
	}

	// ========== Plages d'adresses IP (notation CIDR) ==========

	/**
	 * @param string $ip
	 * @param string $range
	 * @return bool
	 */
	public static function isInRangeOfIpAddressesCidr(string $ip, string $range): bool
	{
		[$subnet, $bits] = explode('/', $range);
		$intIp = ip2long($ip);
		$subnet = ip2long($subnet);
		$mask = -1 << (32 - $bits);
		$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
		return ($intIp & $mask) === $subnet;
	}

	public static function isIpAddressInListOfIpAddress(string $ipAddressToCheck, array $ipAddressList): bool
	{
		foreach ($ipAddressList as $ipAddress) {
			if (self::checkRange($ipAddress)) {
				// C'est une plage d'IP
				if (self::isInRangeOfIpAddressRange($ipAddressToCheck, $ipAddress)) {
					return true;
				}
				continue;
			}

			if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				// c'est un DNS
				$ipAddress = gethostbyname($ipAddress);
			}

			if (self::correspondToIpAddress($ipAddressToCheck, $ipAddress)) {
				return true;
			}
		}
		return false;
	}


}