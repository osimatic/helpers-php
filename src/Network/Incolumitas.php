<?php

namespace Osimatic\Network;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Incolumitas
 * Client for the Incolumitas IP API service
 * @deprecated
 * @see https://github.com/NikolaiT/IP-Address-API
 * @see https://incolumitas.com/pages/IP-API/
 */
class Incolumitas
{
	public const string API_URL = 'https://api.incolumitas.com/';

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private readonly LoggerInterface $logger=new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}

	/**
	 * Retrieves information about an IP address from Incolumitas API
	 * @param string $ipAddress the IP address to check
	 * @return array|null array containing IP information, null if request failed
	 */
	public function getIpInfos(string $ipAddress): ?array
	{
		$url = self::API_URL.'?q='.$ipAddress;
		if (null === ($data = $this->requestExecutor->execute(HTTPMethod::GET, $url, decodeJson: true))) {
			return null;
		}

		return $data;
	}

	/**
	 * Checks if the IP address is using a VPN based on API result
	 * @param array $result the result array from getIpInfos()
	 * @return bool true if VPN is detected, false otherwise
	 */
	public static function isVpn(array $result): bool
	{
		return $result['is_vpn'] ?? false;
	}

	/**
	 * Extracts the ASN (Autonomous System Number) from API result
	 * @param array $result the result array from getIpInfos()
	 * @return int|null ASN number, null if not found
	 */
	public static function getAsn(array $result): ?int
	{
		return $result['asn']['asn'] ?? null;
	}

	/**
	 * Extracts the country code from API result
	 * @param array $result the result array from getIpInfos()
	 * @return string|null country code in uppercase, null if not found
	 */
	public static function getCountryCode(array $result): ?string
	{
		if (empty($countryCode = $result['location']['country_code'] ?? null)) {
			return null;
		}
		return mb_strtoupper($countryCode);
	}
}