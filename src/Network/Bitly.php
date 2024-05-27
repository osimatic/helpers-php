<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Bitly
 * @package Osimatic\Helpers\API
 */
class Bitly
{
	private HTTPClient $httpClient;

	public function __construct(
		private ?string $login=null,
		private ?string $key=null,
		LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

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

		$bitlyUrl = 'http://api.bit.ly/shorten';
		$queryData = [
			'version' => $version,
			'longUrl' => urlencode($url),
			'login' => $this->login,
			'apiKey' => $this->key,
			'format' => 'json'
		];
		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $bitlyUrl, queryData: $queryData))) {
			return null;
		}

		return $json['results'][$url]['shortUrl'] ?? null;
	}
}