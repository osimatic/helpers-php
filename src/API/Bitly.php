<?php

namespace Osimatic\Helpers\API;

use Osimatic\Helpers\Network\HTTPRequest;

/**
 * Class Bitly
 * @package Osimatic\Helpers\API
 */
class Bitly
{
	/**
	 * Bitly constructor.
	 * @param string|null $login
	 * @param string|null $key
	 */
	public function __construct(
		private ?string $login=null,
		private ?string $key=null,
	) {}

	/**
	 * @param string $login
	 * @return self
	 */
	public function setLogin(string $login): self
	{
		$this->login = $login;

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
	 * Utilise l'API de Bit.ly afin de raccourcir une url passée en paramètre.
	 * @param string $url à raccourcir.
	 * @return string|null Url raccourcie, null si une erreur est survenue.
	 */
	public function shorten(string $url): ?string
	{
		$version = '2.0.1';

		$url = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$this->login.'&apiKey='.$this->key.'&format=json';

		if (null === ($res = HTTPRequest::get($url))) {
			return null;
		}

		try {
			$json = \GuzzleHttp\Utils::jsonDecode((string) $res->getBody(), true);
		}
		catch (\Exception $e) {
			return null;
		}

		return $json['results'][$url]['shortUrl'] ?? null;
	}
}