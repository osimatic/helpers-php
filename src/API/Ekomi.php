<?php

namespace Osimatic\API;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Ekomi
 * @package Osimatic\Helpers\API
 */
class Ekomi
{
	public const string URL = 'http://api.ekomi.de/v3/';
	public const string SCRIPT_VERSION = '1.0.0';

	private HTTPClient $httpClient;

	public function __construct(
		private ?string $interfaceId=null,
		private ?string $interfacePassword=null,
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
	 * @param string $interfaceId
	 * @return self
	 */
	public function setInterfaceId(string $interfaceId): self
	{
		$this->interfaceId = $interfaceId;

		return $this;
	}

	/**
	 * @param string $interfacePassword
	 * @return self
	 */
	public function setInterfacePassword(string $interfacePassword): self
	{
		$this->interfacePassword = $interfacePassword;

		return $this;
	}

	/**
	 * @param string|int $orderId
	 * @return string|null
	 */
	public function getFeedbackLink(string|int $orderId): ?string
	{
		$result = $this->executeRequest(self::URL.'putOrder?order_id='.$orderId);

		if (null !== $result) {
			return null;
		}

		return $result['link'] ?? null;
		//return [
		//	'link' 			=> $data['link'],
		//	'hash' 			=> $data['hash'],
		//	'known_since' 	=> $data['known_since'],
		//];
	}

	/**
	 * @param string $range
	 * @return array|null
	 */
	public function getListFeedback(string $range='all'): ?array
	{
		$result = $this->executeRequest(self::URL.'getFeedback?range='.$range);

		if (null === $result) {
			return null;
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	public function getAverage(): ?array
	{
		$result = $this->executeRequest(self::URL.'getSnapshot?range=all');

		if (null === $result) {
			return null;
		}

		return [$result['info']['fb_avg'], $result['info']['fb_count']];
	}

	/**
	 * @param string $url
	 * @return array|null
	 */
	private function executeRequest(string $url): ?array
	{
		$queryData = [
			'auth' => $this->interfaceId.'|'.$this->interfacePassword,
			'version' => 'cust-'.self::SCRIPT_VERSION,
			'type' => 'json',
		];

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url, queryData: $queryData))) {
			return null;
		}

		return $json;
	}

}