<?php

namespace Osimatic\Helpers\API;

use Osimatic\Helpers\Network\HTTPRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://github.com/NikolaiT/IP-Address-API
 * @see https://incolumitas.com/pages/IP-API/
 */
class Incolumitas
{
	public function __construct(
		private LoggerInterface $logger=new NullLogger()
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

	/**
	 * @param string $ipAddress
	 * @return null|array
	 */
	public function getIpInfos(string $ipAddress): ?array
	{
		$url = 'https://api.incolumitas.com/?q='.$ipAddress;

		if (null === ($data = HTTPRequest::getAndDecodeJson($url, [], $this->logger))) {
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