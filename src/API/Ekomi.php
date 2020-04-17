<?php

namespace Osimatic\Helpers\API;

/**
 * Class Ekomi
 * @package Osimatic\Helpers\API
 */
class Ekomi
{
	public const URL = 'http://api.ekomi.de/v3/';
	public const SCRIPT_VERSION = '1.0.0';

	private $interfaceId;
	private $interfacePassword;

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

	private function executeRequest($url): ?array
	{
		$version = 'cust-'.self::SCRIPT_VERSION;
		$auth = $this->interfaceId.'|'.$this->interfacePassword;

		$url .= '&auth='.$auth.'&version='.$version.'&type=json';

		//$result = file_get_contents($url);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);

		if (false === $result) {
			return null;
		}

		//if ($result == 'Access denied') {
		// return null;
		// }

		return json_decode($result, true);
	}

}