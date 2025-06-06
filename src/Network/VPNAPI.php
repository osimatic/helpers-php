<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class VPNAPI
{
	private HTTPClient $httpClient;

	public function __construct(
		private ?string $key=null,
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
	 * @param string $key
	 * @return self
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * @param string $ipAddress
	 * @return null|array
	 */
	public function getIpInfos(string $ipAddress): ?array
	{
		$url = 'https://vpnapi.io/api/'.$ipAddress.'?key='.$this->key;
		if (null === ($data = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		return $data;
	}

	public static function isVpn(array $result): bool
	{
		return $result['security']['vpn'] ?? false;
	}

	public static function getCountryCode(array $result): ?string
	{
		if (empty($countryCode = $result['location']['country_code'] ?? null)) {
			return null;
		}
		return mb_strtoupper($countryCode);
	}

}