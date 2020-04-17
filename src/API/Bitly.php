<?php

namespace Osimatic\Helpers\API;

/**
 * Class Bitly
 * @package Osimatic\Helpers\API
 */
class Bitly
{
	private $login;
	private $key;

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

		$response = file_get_contents($url);

		if ($response === false) {
			return null;
		}

		$json = @json_decode($response, true);
		if (null === $json) {
			return null;
		}

		return $json['results'][$url]['shortUrl'] ?? null;
	}
}