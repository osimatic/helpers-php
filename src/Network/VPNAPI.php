<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class VPNAPI
 * Client for the VPNAPI.io service to detect VPN usage
 */
class VPNAPI
{
	private HTTPClient $httpClient;

	/**
	 * @param string|null $key API key for VPNAPI.io
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $key=null,
		LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->httpClient->setLogger($logger);

		return $this;
	}

	/**
	 * Sets the API key for VPNAPI.io
	 * @param string $key the API key
	 * @return self
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Retrieves information about an IP address from VPNAPI.io
	 * @param string $ipAddress the IP address to check
	 * @return array|null array containing IP information, null if request failed
	 */
	public function getIpInfos(string $ipAddress): ?array
	{
		$url = 'https://vpnapi.io/api/'.$ipAddress.'?key='.$this->key;
		if (null === ($data = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
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
		return $result['security']['vpn'] ?? false;
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