<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://github.com/NikolaiT/IP-Address-API
 * @see https://incolumitas.com/pages/IP-API/
 */
class Incolumitas
{
	private HTTPClient $httpClient;

	public function __construct(
		LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->httpClient->setLogger($logger);

		return $this;
	}

	/**
	 * @param string $ipAddress
	 * @return null|array
	 */
	public function getIpInfos(string $ipAddress): ?array
	{
		$url = 'https://api.incolumitas.com/?q='.$ipAddress;
		if (null === ($data = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		return $data;
	}

	public static function isVpn(array $result): bool
	{
		return $result['is_vpn'] ?? false;
	}

	public static function getAsn(array $result): ?int
	{
		return $result['asn']['asn'] ?? null;
	}

	public static function getCountryCode(array $result): ?string
	{
		if (empty($countryCode = $result['location']['country_code'] ?? null)) {
			return null;
		}
		return mb_strtoupper($countryCode);
	}
}