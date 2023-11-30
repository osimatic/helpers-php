<?php

namespace Osimatic\Helpers\API;

use Osimatic\Helpers\Network\HTTPRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Ekomi
 * @package Osimatic\Helpers\API
 */
class Ekomi
{
	public const URL = 'http://api.ekomi.de/v3/';
	public const SCRIPT_VERSION = '1.0.0';

	public function __construct(
		private ?string $interfaceId=null,
		private ?string $interfacePassword=null,
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
	 * @param $orderId
	 * @return string|null
	 */
	public function getFeedbackLink($orderId): ?string
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
	public function getListFeedback($range='all'): ?array
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
		$version = 'cust-'.self::SCRIPT_VERSION;
		$auth = $this->interfaceId.'|'.$this->interfacePassword;

		$url .= '&auth='.$auth.'&version='.$version.'&type=json';

		if (null === ($json = HTTPRequest::getAndDecodeJson($url, [], $this->logger))) {
			$this->logger->error('Erreur pendant la requete vers l\'API Ekomi.');
			return null;
		}

		return $json;
	}

}