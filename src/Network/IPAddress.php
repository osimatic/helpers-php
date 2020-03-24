<?php

namespace Osimatic\Helpers\Network;

class IPAddress
{
	// ========== Plages d'adresses IP ==========

	/**
	 * @param string $ipAddress
	 * @param string $ipAddressRange
	 * @param string $rangeSeparator
	 * @return bool
	 */
	public static function isInRangeOfIpAddressRange(string $ipAddress, string $ipAddressRange, string $rangeSeparator='-'): bool
	{
		if (strpos($ipAddressRange, $rangeSeparator) === false) {
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
		if ($ipAddress == $ipAddressCompare) {
			return true;
		}

		if (substr($ipAddressCompare, -1) == '%') {
			$debutAdresseIp = substr($ipAddressCompare, 0, -1);
			if (substr($ipAddress, 0, strlen($debutAdresseIp)) == $debutAdresseIp) {
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
		$ip = ip2long($ip);
		$subnet = ip2long($subnet);
		$mask = -1 << (32 - $bits);
		$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
		return ($ip & $mask) === $subnet;
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