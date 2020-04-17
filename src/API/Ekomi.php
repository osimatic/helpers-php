<?php

namespace Osimatic\Helpers\API;

class Ekomi
{
	const SCRIPT_VERSION = '1.0.0';

	private $interfaceId;
	private $interfacePassword;

	/**
	 * @param string $interfaceId
	 */
	public function setInterfaceId(string $interfaceId): void
	{
		$this->interfaceId = $interfaceId;
	}

	/**
	 * @param string $interfacePassword
	 */
	public function setInterfacePassword(string $interfacePassword): void
	{
		$this->interfacePassword = $interfacePassword;
	}

	public function getFeedbackLink($orderId): ?string
	{
		$version = 'cust-'.self::SCRIPT_VERSION;

		$url = 'http://api.ekomi.de/v3/putOrder?auth='.$this->getAuth().'&version='.$version.'&order_id='.$orderId.'&type=json';
		//$result = file_get_contents($url);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);

		$data = json_decode($result, true);

		if (null !== $data) {
			//if ($result == 'Access denied') {
			return null;
		}

		return $data['link'] ?? null;
		//return [
		//	'link' 			=> $data['link'],
		//	'hash' 			=> $data['hash'],
		//	'known_since' 	=> $data['known_since'],
		//];
	}

	private function getAuth(): string
	{
		return $this->interfaceId.'|'.$this->interfacePassword;
	}

}